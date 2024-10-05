<?php

use SimonSchaufi\Autologin\Domain\Model\Renderable\SetConfirmUrl;
use SimonSchaufi\Autologin\Domain\Model\Renderable\SetUniqueHash;
use SimonSchaufi\Autologin\Service\AutoLoginService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/*
|--------------------------------------------------------------------------
| EXT felogin
|--------------------------------------------------------------------------
*/
ExtensionManagementUtility::addService(
    'autologin',
    'auth',
    AutoLoginService::class,
    [
        'title' => 'Auto login for users',
        'description' => 'Authenticates user with given session value',
        'subtype' => 'getUserFE,authUserFE',
        'available' => true,
        'priority' => 75,
        'quality' => 75,
        'os' => '',
        'exec' => '',
        'className' => AutoLoginService::class,
    ]
);

/*
|--------------------------------------------------------------------------
| EXT form: Registration
|--------------------------------------------------------------------------
*/
/* @see \TYPO3\CMS\Form\Domain\Runtime\FormRuntime::mapAndValidatePage() */
// Set unique hash
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterSubmit'][1728070553] = SetUniqueHash::class;

// Set the confirmation URL
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterSubmit'][1728070562] = SetConfirmUrl::class;
