<?php

declare(strict_types=1);

namespace SimonSchaufi\Autologin\EventListener;

use TYPO3\CMS\Core\Authentication\Event\BeforeRequestTokenProcessedEvent;
use TYPO3\CMS\Core\Security\RequestToken;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

final class BeforeRequestTokenProcessedListener
{
    public function __invoke(BeforeRequestTokenProcessedEvent $event): void
    {
        $requestToken = $event->getRequestToken();
        // fine, there is a valid request token
        if ($requestToken instanceof RequestToken) {
            return;
        }

        $user = $event->getUser();
        if (!$user instanceof FrontendUserAuthentication) {
            return;
        }
        $requestToken = RequestToken::create('core/user-auth/' . strtolower($user->loginType));
        $event->setRequestToken($requestToken);
    }
}
