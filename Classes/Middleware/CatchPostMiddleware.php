<?php

namespace Cleantalk\Classes\Middleware;

use Cleantalk\Custom\Helper\Helper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CatchPostMiddleware implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->needProcess($request)) {
            $helper = new Helper();
            $flexformService = GeneralUtility::makeInstance(FlexFormService::class);
            dd($request->get());
//            $ct_temp_msg_data = $helper::get_fields_any($request->request->all());
//            $spam_check = array();
//            $spam_check['type'] = 'custom_contact_form';
//            $spam_check['sender_email'] = ($ct_temp_msg_data['email']    ? $ct_temp_msg_data['email']    : '');
//            $spam_check['sender_nickname'] = ($ct_temp_msg_data['nickname'] ? $ct_temp_msg_data['nickname'] : '');
//            $spam_check['message_title'] = ($ct_temp_msg_data['subject']  ? $ct_temp_msg_data['subject']  : '');
//            $spam_check['message_body'] = ($ct_temp_msg_data['message']  ? implode("\n", $ct_temp_msg_data['message'])  : '');
//
//            if ($spam_check['sender_email'] != '' || $spam_check['message_title'] != '' || $spam_check['message_body'] != '') {
//
//                $result = CleantalkFuncs::_cleantalk_check_spam($spam_check);
//
//                if (isset($result) && is_array($result) && $result['errno'] == 0 && $result['allow'] != 1) {
//                    \Drupal::messenger()->addError(HTML::escape($result['ct_result_comment']));
//                }
//            }
        }

        return $handler->handle($request);
    }

    private function needProcess(ServerRequestInterface $request): bool
    {
        if (
            $request->getMethod() === 'POST' &&
            $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cleantalk']['enablePlugin'] &&
            $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cleantalk']['accessKey']
        ) {
            return true;
        }

        return false;
    }
}