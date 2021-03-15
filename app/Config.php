<?php

namespace App;

use PDO;

class Config
{
  public const TOKEN_LIFETIME = 3600 * 24 * 7; // 1 week

  public const PDO_OPTIONS = [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ];

  public static function ENV_IS_DEV()
  {
    return $_ENV['APP_ENV'] === 'dev';
  }
}
