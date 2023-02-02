<?php

namespace Cleantalk\Custom\Api;

class Api extends \Cleantalk\Common\Api\Api
{
  /**
   * Function sends empty feedback for version comparison in Dashboard
   *
   * @param  string api_key
   * @param  string agent-version
   * @param  bool perform check flag
   * @return mixed (STRING || array('error' => true, 'error_string' => STRING))
   */
  public static function methodSendFeedback($api_key, $agent, $do_check = true)
  {
    $request = array(
      'method_name' => 'send_feedback',
      'auth_key' => $api_key,
      'feedback' => 0 . ':' . $agent,
    );

    return static::sendRequest($request);
  }

}
