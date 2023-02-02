<?php

namespace Cleantalk\Custom\Cron;

class Cron extends \Cleantalk\Common\Cron\Cron
{
  public function getDefaultTasks()
    {
      return [
        'sfw_update' => [
          'handler'   => '\Drupal\cleantalk\CleantalkFuncs::apbct_sfw_update',
          'next_call' => time() + 60,
          'period'    => 86400,
          'params'    => [],
        ],
        'sfw_send_logs' => [
          'handler'   => '\Drupal\cleantalk\CleantalkFuncs::apbct_sfw_send_logs',
          'next_call' => time() + 3600,
          'period'    => 3600,
          'params'    => [],
        ],
        'sfw_ac__clear_table' => [
          'handler'   => '\Drupal\cleantalk\CleantalkFuncs::apbct_sfw_ac__clear_table',
          'next_call' => time() + 3600,
          'period'    => 3600,
          'params'    => [],
        ]
      ];
    }
}
