<?php
defined('TYPO3_MODE') or die();


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler (
        '\TYPO3\CMS\Core\Frontend::registerForm',
        '\TYPO3\CMS\Cleantalk\Hook\InitFEuser::class->hookRegisterData'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler (
        '\TYPO3\CMS\Core\Frontend::commentForm',
        '\TYPO3\CMS\Cleantalk\Hook\InitFEuser::class->hookCommentData'
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['initFEuser'][] = \TYPO3\CMS\Cleantalk\Hook\InitFEuser::class . '->hookPostData';