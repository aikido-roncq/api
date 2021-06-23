<?php

namespace App\Exceptions;

use Utils\Http;

class LoggedOutException extends HttpException
{
  public function __construct(string $message)
  {
    parent::__construct($message, Http::UNAUTHORIZED);
  }
}
