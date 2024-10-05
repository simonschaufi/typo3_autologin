<?php

declare(strict_types=1);

namespace SimonSchaufi\Autologin\Domain\Finishers;

use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;

/**
 * This finisher for form framework will crypt the configured field with salted
 * passwords, if enabled for frontend.
 *
 * @see https://daniel-siepmann.de/Posts/2017/2017-08-26-typo3-form-custom-finisher-to-crypt-values.html
 */
class CryptFinisher extends AbstractFinisher
{
    protected $defaultOptions = [
        'field' => 'password',
    ];

    public function __construct(
        private readonly PasswordHashFactory $passwordHashFactory
    ) {
    }

    protected function executeInternal(): void
    {
        $fieldName = $this->parseOption('field');
        $formValues = $this->finisherContext->getFormValues();
        if (!isset($formValues[$fieldName])) {
            return;
        }

        $hashInstance = $this->passwordHashFactory->getDefaultHashInstance('FE');

        $this->finisherContext->getFinisherVariableProvider()->add(
            $this->shortFinisherIdentifier,
            $fieldName,
            $hashInstance->getHashedPassword($formValues[$fieldName])
        );
    }
}
