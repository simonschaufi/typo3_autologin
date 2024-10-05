<?php

declare(strict_types=1);

$GLOBALS['SiteConfiguration']['site']['columns']['autologinSuccessPage'] = [
    'label' => 'LLL:EXT:typo3_autologin/Resources/Private/Language/locallang_siteconfiguration_tca.xlf:site.autologinSuccessPage',
    'description' => 'LLL:EXT:typo3_autologin/Resources/Private/Language/siteconfiguration_fieldinformation.xlf:site.autologinSuccessPage',
    'config' => [
        'type' => 'input',
        'eval' => 'trim, int',
        'size' => 8,
    ],
];
$GLOBALS['SiteConfiguration']['site']['columns']['autologinErrorPage'] = [
    'label' => 'LLL:EXT:typo3_autologin/Resources/Private/Language/locallang_siteconfiguration_tca.xlf:site.autologinErrorPage',
    'description' => 'LLL:EXT:typo3_autologin/Resources/Private/Language/siteconfiguration_fieldinformation.xlf:site.autologinErrorPage',
    'config' => [
        'type' => 'input',
        'eval' => 'trim, int',
        'size' => 8,
    ],
];

$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] .= ',--div--;Autologin,autologinSuccessPage,autologinErrorPage';
