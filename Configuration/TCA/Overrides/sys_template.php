<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::addStaticFile(
    'typo3_autologin',
    'Configuration/TypoScript/',
    'Autologin'
);
