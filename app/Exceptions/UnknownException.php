<?php

namespace App\Exceptions;

use Utils\Http;

/**
 * Exception thrown when a unknown error occurs
 */
class UnknownException extends HttpException
{
  public function __construct(string $message)
  {
    parent::__construct($message, Http::INTERNAL_SERVER_ERROR);
  }
}
