<?php
namespace Cleantalk\Classes\Controller;

use In2code\Powermail\Controller\FormController as FormControllerOrigin;
use TYPO3\CMS\Core\Mail\MailMessage;

use Cleantalk\Common\Cleaner\Sanitize;
use Cleantalk\Common\Variables\Server;
use Cleantalk\Custom\Antispam\Cleantalk;
use Cleantalk\Common\Antispam\CleantalkRequest;
use Cleantalk\Custom\Helper\Helper;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * SendMailService
 *
 * @package powermailextend
 */
class FormController
{

    /**
     * Manipulate message object short before powermail send the mail
     *
     * @param MailMessage $message
     * @param array $email
     * @param FormController $originalService
     */
    public function spamCheck($email, $hash, FormControllerOrigin $originalService)
    {
        global $cleantalk_execute;

        if (!$cleantalk_execute && $this->needProcess()) {
            $filtered_form_data = Helper::get_fields_any($_POST);
            $spam_check = array();
            $spam_check['comment_type'] = 'powermail_contact_form';
            $spam_check['sender_email'] = !empty($filtered_form_data['email']) ? $filtered_form_data['email'] : '';
            $spam_check['sender_nickname'] = !empty($filtered_form_data['nickname']) ? $filtered_form_data['nickname'] : '';
            $spam_check['message_title'] = !empty($filtered_form_data['subject']) ? $filtered_form_data['subject'] : '';
            $spam_check['sender_info']['REFFERRER'] = Server::get('HTTP_REFERER');

            foreach ($filtered_form_data['message'] as $key => $value) {
                if (preg_match('/^tx_powermail.+_field_text$/', $key)){
                    $spam_check['message'] = $value;
                    break;
                }
            }

            $spam_check['event_token'] = !empty($_POST['ct_bot_detector_event_token'])
                ? $_POST['ct_bot_detector_event_token']
                : null;

            $spam_check['js_on'] = (bool)($spam_check['event_token']);

            if ($spam_check['sender_email'] || $spam_check['message_title'] || $spam_check['message_body']) {
                $default_params = Helper::getDefaultRequestParams();
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

        return;
    }

    private function needProcess(): bool
    {
        if (
            !empty('POST') &&
            $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cleantalk']['enablePlugin'] &&
            $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cleantalk']['accessKey']
        ) {
            return true;
        }
        return false;
    }

}
