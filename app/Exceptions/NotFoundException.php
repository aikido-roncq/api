<?php

namespace App\Exceptions;

use Utils\Http;

class NotFoundException extends HttpException
{
  public function __construct(string $message)
  {
    parent::__construct($message, Http::NOT_FOUND);
  }
}
