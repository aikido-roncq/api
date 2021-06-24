<?php

namespace App;

use PDO;

/**
 * Configuration class
 */
class Config
{
  /**
   * The token lifetime (1 month)
   */
  public const TOKEN_LIFETIME = 3600 * 24 * 30;

  /**
   * PDO default options
   */
  public const PDO_OPTIONS = [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ];

  /**
   * Whether or not the current environment is dev
   * 
   * @return bool true if the environment is dev
   */
  public static function ENV_IS_DEV(): bool
  {
    return $_ENV['APP_ENV'] === 'dev';
  }
}
