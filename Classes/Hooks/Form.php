<?php

declare(strict_types=1);

namespace Cleantalk\Classes\Hooks;

use Cleantalk\Common\Cleaner\Sanitize;
use Cleantalk\Common\Variables\Server;
use Cleantalk\Custom\Antispam\Cleantalk;
use Cleantalk\Common\Antispam\CleantalkRequest;
use Cleantalk\Custom\Helper\Helper;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

class Form
{
    public function afterSubmit(FormRuntime $runtime, $element, $value, $requestArguments)
    {
        global $cleantalk_execute;

        if ($this->needProcess($runtime) && !$cleantalk_execute) {
            $filtered_form_data = Helper::get_fields_any($requestArguments);

            $spam_check = array();
            $spam_check['comment_type'] = 'standard_contact_form';
            $spam_check['sender_email'] = !empty($filtered_form_data['email']) ? $filtered_form_data['email'] : '';
            $spam_check['sender_nickname'] = !empty($filtered_form_data['nickname']) ? $filtered_form_data['nickname'] : '';
            $spam_check['message_title'] = !empty($filtered_form_data['subject']) ? $filtered_form_data['subject'] : '';
            $spam_check['message'] = !empty($filtered_form_data['message']) && is_array($filtered_form_data['message'])
                ? implode("\n", $filtered_form_data['message'])
                : '';

            $spam_check['event_token'] = !empty($runtime->getRequest()->getParsedBody()['ct_bot_detector_event_token'])
                ? $runtime->getRequest()->getParsedBody()['ct_bot_detector_event_token']
                : null;

            if ($spam_check['sender_email'] || $spam_check['message_title'] || $spam_check['message_body']) {
                $default_params = $this->getDefaultRequestParams(Helper::class);
                $ct         = new Cleantalk();
                $ct_request = new CleantalkRequest(
                    Helper::arrayMergeSaveNumericKeysRecursive($default_params, $spam_check)
                );

                $request_result = $ct->isAllowMessage($ct_request);

                if ($request_result->allow == 0) {
                    $ct_die_page = file_get_contents($ct::getLockPageFile());

                    // Translation
                    $replaces = array(
                        '{MESSAGE_TITLE}' => 'Spam protection',
                        '{MESSAGE}'       => $request_result->comment,
                        '{BACK_LINK}'     => '<a href="' . Sanitize::cleanUrl(Server::get('HTTP_REFERER')) . '">Back</a>',
                        '{BACK_SCRIPT}'   => '<script>setTimeout("history.back()", 5000);</script>'
                    );

                    foreach ( $replaces as $place_holder => $replace ) {
                        $ct_die_page = str_replace($place_holder, $replace, $ct_die_page);
                    }

                    http_response_code(200);
                    die($ct_die_page);
                }
            }
            $cleantalk_execute = true;
        }

        return $value;
    }

    private function needProcess(FormRuntime $runtime): bool
    {
        if (
            $runtime->getRequest()->getMethod() === 'POST' &&
            $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cleantalk_antispam']['enablePlugin'] &&
            $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cleantalk_antispam']['accessKey']
        ) {
            return true;
        }

        return false;
    }

    private function getDefaultRequestParams($helper): array
    {
        return [
            'sender_ip' => $helper::ipGet('remote_addr', false),
            'x_forwarded_for' => $helper::ipGet('x_forwarded_for', false),
            'x_real_ip'       => $helper::ipGet('x_real_ip', false),
            'auth_key'        => $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cleantalk_antispam']['accessKey'],
            'agent'       => 'typo3-1.0.0',
        ];
    }
}