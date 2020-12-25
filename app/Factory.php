<?php

namespace App;

use PDO;

class Factory
{
    /**
     * @var PDO
     */
    private static $_pdo;

    public static function pdo(): PDO
    {
        if (is_null(self::$_pdo)) {
            $DB_HOST = $_ENV['DB_HOST'];
            $DB_NAME = $_ENV['DB_NAME'];
            $DB_USER = $_ENV['DB_USER'];
            $DB_PASS = $_ENV['DB_PASS'];
            $DSN = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

            self::$_pdo = new PDO($DSN, $DB_USER, $DB_PASS, Config::optsPDO());
        }

        return self::$_pdo;
    }
}
