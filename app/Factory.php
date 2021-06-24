<?php

namespace App;

use PDO;

/**
 * Factory class
 */
abstract class Factory
{
  /**
   * The PDO singleton
   */
  private static ?PDO $pdo = null;

  /**
   * Get the PDO instance (if it does not exist, create it)
   * 
   * @return PDO the PDO instance
   */
  public static function pdo(): PDO
  {
    if (is_null(self::$pdo)) {
      $DB_HOST = $_ENV['DB_HOST'];
      $DB_NAME = $_ENV['DB_NAME'];
      $DB_USER = $_ENV['DB_USER'];
      $DB_PASS = $_ENV['DB_PASS'];
      $DSN = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

      self::$pdo = new PDO($DSN, $DB_USER, $DB_PASS, Config::PDO_OPTIONS);
    }

    return self::$pdo;
  }
}
