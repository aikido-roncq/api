<?php

namespace App\Exceptions;

use Utils\Http;

class UnknownException extends HttpException
{
  public function __construct(string $message)
  {
    parent::__construct($message, Http::INTERNAL_SERVER_ERROR);
  }
}
