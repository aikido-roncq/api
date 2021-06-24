<?php

namespace App\Exceptions;

use Utils\Http;

/**
 * Exception thrown when the user is logged out
 */
class LoggedOutException extends HttpException
{
  public function __construct(string $message)
  {
    parent::__construct($message, Http::UNAUTHORIZED);
  }
}
