<?php namespace Zara4\LazyMySql;

use Illuminate\Support\Arr;


class Config {


  /**
   * @param array $config
   * @return bool
   */
  public static function hasReadConfig(array $config) {
    return isset($config['read']);
  }


  /**
   * @param array $config
   * @return bool
   */
  public static function hasWriteConfig(array $config) {
    return isset($config['write']);
  }


  /**
   * Get the read configuration for a read / write connection.
   *
   * @param  array  $config
   * @return array
   */
  public static function getReadConfig(array $config) {

    $readConfig = self::getReadWriteConfig($config, 'read');

    if (isset($readConfig['host']) && is_array($readConfig['host'])) {
      $readConfig['host'] = count($readConfig['host']) > 1
        ? $readConfig['host'][array_rand($readConfig['host'])]
        : $readConfig['host'][0];
    }

    return self::mergeReadWriteConfig($config, $readConfig);
  }


  /**
   * Get the read configuration for a read / write connection.
   *
   * @param  array  $config
   * @return array
   */
  public static function getWriteConfig(array $config) {

    $writeConfig = self::getReadWriteConfig($config, 'write');

    return self::mergeReadWriteConfig($config, $writeConfig);
  }


  /**
   * Get a read / write level configuration.
   *
   * @param  array   $config
   * @param  string  $type
   * @return array
   */
  public static function getReadWriteConfig(array $config, $type) {

    if (isset($config[$type][0])) {
      return $config[$type][array_rand($config[$type])];
    }

    return $config[$type];
  }


  /**
   * Merge a configuration for a read / write connection.
   *
   * @param  array  $config
   * @param  array  $merge
   * @return array
   */
  public static function mergeReadWriteConfig(array $config, array $merge) {
    return Arr::except(array_merge($config, $merge), ['read', 'write']);
  }


  /**
   * Parse and prepare the database configuration.
   *
   * @param  array   $config
   * @param  string  $name
   * @return array
   */
  public static function parseConfig(array $config, $name) {
    return Arr::add(Arr::add($config, 'prefix', ''), 'name', $name);
  }

} 