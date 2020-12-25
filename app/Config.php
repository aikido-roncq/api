<?php

namespace App;

use PDO;

class Config
{
    const TOKEN_LIFETIME = 3600 * 24 * 7; // 1 week

    const PDO_OPTIONS = [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];

    public static function optsPDO()
    {
        $opts = self::PDO_OPTIONS;

        if ($_ENV['APP_ENV'] != 'prod')
            $opts[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

        return $opts;
    }
}
