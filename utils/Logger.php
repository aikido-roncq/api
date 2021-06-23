<?php

namespace Utils;

class Logger
{

  private const INFO = 'INFO';
  private const ERROR = 'ERROR';

  public static function info(string $message): void
  {
    self::log(self::INFO, $message);
  }

  public static function error(string $message): void
  {
    self::log(self::ERROR, $message);
  }


  private static function log(string $level, string $message): void
  {
    error_log("[$level] $message");
  }
}
