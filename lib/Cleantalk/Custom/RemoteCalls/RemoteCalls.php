<?php

namespace Cleantalk\Custom\RemoteCalls;

use Cleantalk\Common\Variables\Get;

class RemoteCalls extends \Cleantalk\Common\RemoteCalls\RemoteCalls
{
  protected $available_rc_actions = array(
    'sfw_update' => array(
      'last_call' => 0,
      'cooldown' => 0
    ),
    'sfw_send_logs' => array(
      'last_call' => 0,
      'cooldown' => self::COOLDOWN
    )
  );

    /**
     * SFW update
     *
     * @return string
     */
    public function action__sfw_update()
    {
        return \Drupal\cleantalk\CleantalkFuncs::apbct_sfw_update($this->api_key);
    }

    /**
     * SFW send logs
     *
     * @return string
     */
    public function action__sfw_send_logs()
    {
        return \Drupal\cleantalk\CleantalkFuncs::apbct_sfw_send_logs($this->api_key);
    }

    public function action__sfw_update__write_base()
    {
        return \Drupal\cleantalk\CleantalkFuncs::apbct_sfw_update($this->api_key);
    }

    /**
     * Get available remote calls from the storage.
     *
     * @return array
     */
    protected function getAvailableRcActions()
    {
        $remote_calls = \Drupal::state()->get('cleantalk_remote_calls');
        if ($remote_calls && !empty($remote_calls)) {
            return empty(array_diff_key($remote_calls, $this->available_rc_actions)) ? $remote_calls : $this->available_rc_actions;
        }
        return $this->available_rc_actions;
    }

    /**
     * Set last call timestamp and save it to the storage.
     *
     * @param string $action
     *
     * @return void
     */
    protected function setLastCall($action)
    {
        // TODO: Implement setLastCall() method.
        $remote_calls = $this->getAvailableRcActions();
        $remote_calls[$action]['last_call'] = time();
        \Drupal::state()->set('cleantalk_remote_calls', $remote_calls);
    }

}
