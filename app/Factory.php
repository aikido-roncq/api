<?php

namespace App;

use PDO;

abstract class Factory
{
  private static ?PDO $pdo = null;

  public static function pdo(): PDO
  {
    if (is_null(self::$pdo)) {
      $DB_HOST = getenv('DB_HOST');
      $DB_NAME = getenv('DB_NAME');
      $DB_USER = getenv('DB_USER');
      $DB_PASS = getenv('DB_PASS');
      $DSN = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

      self::$pdo = new PDO($DSN, $DB_USER, $DB_PASS, Config::PDOopts());
    }

    return self::$pdo;
  }
}
