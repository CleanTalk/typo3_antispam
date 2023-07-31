<?php

use Cleantalk\Classes\Hooks\Form;

defined('TYPO3') or die();

(static function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterSubmit'][] = Form::class;
})();

if (
    class_exists('\TYPO3\CMS\Core\Utility\GeneralUtility') &&
    class_exists('In2code\Powermail\Controller\FormController')
) {
    call_user_func(function () {
        /**
         * Register some Slots
         */
        /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
        $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class
        );

        // Change receiver mail example
        $signalSlotDispatcher->connect(
            In2code\Powermail\Controller\FormController::class,
            'createActionBeforeRenderView',
            \Cleantalk\Classes\Controller\FormController::class,
            'spamCheck',
            false
        );
    });
}






