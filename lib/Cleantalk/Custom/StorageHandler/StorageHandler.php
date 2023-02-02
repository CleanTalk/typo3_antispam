<?php

namespace Cleantalk\Custom\StorageHandler;

class StorageHandler implements \Cleantalk\Common\StorageHandler\StorageHandler
{
  public static $jsLocation;

  public static function getSetting($setting_name)
  {
    return \Drupal::state()->get($setting_name);
  }

  public static function deleteSetting($setting_name)
  {
    return \Drupal::state()->delete($setting_name);
  }

  public static function saveSetting($setting_name, $setting_value)
  {
    return \Drupal::state()->set($setting_name, $setting_value);
  }

  public static function getUpdatingFolder()
  {
    return APBCT_DIR_PATH . DIRECTORY_SEPARATOR . 'cleantalk_fw_files' . DIRECTORY_SEPARATOR;
  }

  public static function getJsLocation()
  {
    if ( ! empty( static::$jsLocation ) ) {
      return static::$jsLocation;
    }
    return \Drupal::request()->getSchemeAndHttpHost() . "/modules/cleantalk/js/apbct-functions.js";
  }
}
