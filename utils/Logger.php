<?php

namespace Utils;

/**
 * A logger to display logs
 */
class Logger
{
  /**
   * Debug log
   */
  private const DEBUG = 'DEBUG';

  /**
   * Info log
   */
  private const INFO = 'INFO';

  /**
   * Error log
   */
  private const ERROR = 'ERROR';

  /**
   * Create a debug log
   */
  public static function debug(string $message): void
  {
    self::log(self::DEBUG, $message);
  }

  /**
   * Create an info log
   * 
   * @param string $message the message to log
   */
  public static function info(string $message): void
  {
    self::log(self::INFO, $message);
  }

  /**
   * Create an error log
   * 
   * @param string $message the message to log
   */
  public static function error(string $message): void
  {
    self::log(self::ERROR, $message);
  }

  /**
   * Log a message with a given level
   * 
   * @param string $level the error level (INFO, ERROR...)
   * @param string $message the message to log
   */
  private static function log(string $level, string $message): void
  {
    error_log("[$level] $message");
  }
}
