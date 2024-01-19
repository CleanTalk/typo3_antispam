<?php

namespace Cleantalk\Classes\Middleware;

use Cleantalk\Common\Antispam\CleantalkRequest;
use Cleantalk\Common\Cleaner\Sanitize;
use Cleantalk\Common\Variables\Server;
use Cleantalk\Custom\Antispam\Cleantalk;
use Cleantalk\Custom\Helper\Helper;
use Psr\Http\Message\ServerRequestInterface;

class cleantalkIntegrations
{
    /**
     * Common way to check spam with presented required values
     * @param cleantalkIntegrationData $integration_data
     */
    public function integrationSpamCheck(cleantalkIntegrationData $integration_data)
    {
        global $cleantalk_execute;

        if ( !$cleantalk_execute && $this->needProcess() ) {
            $spam_check = array();
            //from integration data
            $spam_check['comment_type'] = $integration_data->integration_name;
            $spam_check['sender_email'] = $integration_data->email;
            $spam_check['sender_nickname'] = $integration_data->username;
            $spam_check['message'] = $integration_data->message;
            $spam_check['event_token'] = $integration_data->event_token;

            //other sources
            $spam_check['js_on'] = (bool)($spam_check['event_token']);
            $spam_check['sender_info']['REFFERRER'] = Server::get('HTTP_REFERER');

            // run check process
            $default_params = Helper::getDefaultRequestParams();
            $ct = new Cleantalk();
            $ct_request = new CleantalkRequest(
                Helper::arrayMergeSaveNumericKeysRecursive($default_params, $spam_check)
            );

            $request_result = $ct->isAllowMessage($ct_request);

            //if blocked
            if ( $request_result->allow == 0 ) {
                $ct_die_page = file_get_contents($ct::getLockPageFile());

                // Translation
                $replaces = array(
                    '{MESSAGE_TITLE}' => 'Spam protection',
                    '{MESSAGE}' => $request_result->comment,
                    '{BACK_LINK}' => '<a href="' . Sanitize::cleanUrl(Server::get('HTTP_REFERER')) . '">Back</a>',
                    '{BACK_SCRIPT}' => '<script>setTimeout("history.back()", 5000);</script>'
                );

                foreach ( $replaces as $place_holder => $replace ) {
                    $ct_die_page = str_replace($place_holder, $replace, $ct_die_page);
                }

                http_response_code(200);
                die($ct_die_page);
            }

            $cleantalk_execute = true;
        }
    }

    public function getIntegrationData(ServerRequestInterface $request)
    {
        $ct_integration = new cleantalkIntegrationData();
        try {
            //FE Manger
            if ( $request->getMethod() === 'POST' && !isset($request->getParsedBody()['tx_femanager_pi1']['validation']) ) {
                $ct_integration->integration_name = '_fe_manager';
                $post = $request->getParsedBody();
                if ( isset($post['tx_femanager_pi1']) && is_array($post['tx_femanager_pi1']) ) {
                    $ct_integration->event_token = $post['ct_bot_detector_event_token'];
                    if ( isset($post['tx_femanager_pi1']['user']) && is_array($post['tx_femanager_pi1']['user']) ) {
                        $ct_integration->username = $post['tx_femanager_pi1']['user']['username'];
                        $ct_integration->email = $post['tx_femanager_pi1']['user']['email'];
                    }
                }
                return $ct_integration;
            }

            //do new integrations there

        } catch ( \Exception $e ) {
            error_log('Cleantalk Antispam error: ' . $e);
        }
        return false;
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
