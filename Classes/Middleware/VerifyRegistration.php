<?php

declare(strict_types=1);

namespace SimonSchaufi\Autologin\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SimonSchaufi\Autologin\Utility\LoginUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Extbase\Exception as ExtbaseException;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Typolink\PageLinkBuilder;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;
use SimonSchaufi\Autologin\Utility\RegistrationTokenUtility;

/**
 * This class handles the email verification process after registration
 */
class VerifyRegistration implements MiddlewareInterface
{
    public function __construct(
        private readonly ContentObjectRenderer $contentObjectRenderer,
        private readonly LoginUtility $loginUtility
    ) {
    }

    /**
     * @throws ExtbaseException
     * @throws UnableToLinkException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getQueryParams()['reg_token'] ?? null;
        if ($token === null) {
            return $handler->handle($request);
        }

        $tokenPayload = RegistrationTokenUtility::decode($token);
        

        $userUid = $this->getUserId($tokenPayload['sub']);
        if ($userUid === false) {
            // unset uniquehash, otherwise we will end up in a redirect loop
            $request = $request->withQueryParams([]);
            return $this->redirectToErrorPage($request);
        }

        $this->activateUser((int)$userUid);

        // Activate Autologin
        $this->loginUtility->setAutologinHmac((int)$userUid);
        RegistrationTokenUtility::markAsUsed($tokenPayload);

        return $this->redirectToSuccessPage($request);
    }

    private function getUserId(string $uniqueHash)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('fe_users');

        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder
            ->select('uid')
            ->from('fe_users')
            ->where(
                $queryBuilder->expr()->eq('uniquehash', $queryBuilder->createNamedParameter($uniqueHash))
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();
    }

    private function activateUser(int $userUid): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('fe_users');

        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $queryBuilder
            ->update('fe_users')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($userUid, \PDO::PARAM_INT))
            )
            ->set('disable', 0)
            ->set('uniquehash', '')
            ->set('tstamp', GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp'))
            ->executeStatement();
    }

    /**
     * @throws ExtbaseException
     * @throws UnableToLinkException
     */
    private function redirectToSuccessPage(ServerRequestInterface $request): ResponseInterface
    {
        $request = $request->withQueryParams([
            'logintype' => 'login',
            'autologin' => '1',
        ]);

        $site = $request->getAttribute('site');
        $autologinSuccessPage = (int)$site->getAttribute('autologinSuccessPage');
        return $this->generateLink($request, $autologinSuccessPage);
    }

    /**
     * @throws ExtbaseException
     * @throws UnableToLinkException
     */
    private function redirectToErrorPage(ServerRequestInterface $request): ResponseInterface
    {
        $site = $request->getAttribute('site');
        $errorPage = (int)$site->getAttribute('autologinErrorPage');
        return $this->generateLink($request, $errorPage);
    }

    /**
     * Generate link based on current page information
     *
     * @throws UnableToLinkException
     */
    private function generateLink(ServerRequestInterface $request, int $pageUid): ResponseInterface
    {
        $linkDetails = [
            'pageuid' => $pageUid,
        ];
        $configuration = [
            'parameter' => 't3://page?uid=' . $pageUid,
            'forceAbsoluteUrl' => true,
            'linkAccessRestrictedPages' => true,
            'additionalParams' => HttpUtility::buildQueryString($request->getQueryParams(), '&'),
        ];

        $linkBuilder = GeneralUtility::makeInstance(PageLinkBuilder::class, $this->contentObjectRenderer);
        $result = $linkBuilder->build($linkDetails, '', '', $configuration);
        $url = new Uri($result->getUrl());

        return $this->buildRedirectResponse($url);
    }

    /**
     * Creates a PSR-7 compatible Response object
     */
    private function buildRedirectResponse(UriInterface $uri): ResponseInterface
    {
        return new RedirectResponse(
            $uri,
            301,
            [
                'X-Redirect-By' => 'TYPO3 Redirect from VerifyRegistration Middleware',
            ]
        );
    }
}
