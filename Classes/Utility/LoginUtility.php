<?php

declare(strict_types=1);

namespace SimonSchaufi\Autologin\Utility;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LoginUtility implements SingletonInterface
{
    public function __construct(
        private readonly Context $context,
        private readonly Registry $registry
    ) {
    }

    /**
     * @throws AspectNotFoundException
     */
    public function setAutologinHmac(int $userId): void
    {
        session_start();

        $_SESSION['typo3-autologin-user'] = GeneralUtility::hmac('auto-login::' . $userId, $this->context->getPropertyFromAspect('date', 'timestamp'));

        $this->registry->set('typo3-autologin-hmac', $_SESSION['typo3-autologin-user'], $userId);
    }
}
