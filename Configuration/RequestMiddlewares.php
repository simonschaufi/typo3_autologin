<?php

declare(strict_types=1);

use SimonSchaufi\Autologin\Middleware\VerifyRegistration;

return [
    'frontend' => [
        'typo3-autologin/verify-registration' => [
            'target' => VerifyRegistration::class,
            'before' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
            'after' => [
                'typo3/cms-frontend/output-compression',
            ],
        ],
    ],
];
