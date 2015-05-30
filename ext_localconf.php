<?php
defined('TYPO3_MODE') or die();

// Add a hook to the BE login form
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['initFEuser'][] = \TYPO3\CMS\Cleantalk\Hook\InitFEuser::class . '->hookPostData';
//$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/template.php']['preStartPageHook'][] = \TYPO3\CMS\Cleantalk\Hook\PreStartPageHook::class . '->hookPostData';