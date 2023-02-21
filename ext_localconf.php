<?php

use Cleantalk\Classes\Hooks\Form;

defined('TYPO3') or die();

(static function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterSubmit'][] = Form::class;
})();