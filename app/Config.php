<?php

namespace App;

use PDO;

class Config
{
  public const TOKEN_LIFETIME = 3600 * 24 * 7; // 1 week

  private const PDO_DEFAULT_OPTIONS = [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ];

  public static function PDOopts()
  {
    if (self::ENV_IS_DEV())
      return self::PDO_DEFAULT_OPTIONS + [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

    return self::PDO_DEFAULT_OPTIONS;
  }

  public static function ENV_IS_DEV()
  {
    return $_ENV['APP_ENV'] === 'dev';
  }
}
