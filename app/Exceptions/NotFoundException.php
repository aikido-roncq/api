<?php

namespace App\Exceptions;

use Utils\Http;

/**
 * Exception thrown when the requested resource was not found
 */
class NotFoundException extends HttpException
{
  public function __construct(string $message)
  {
    parent::__construct($message, Http::NOT_FOUND);
  }
}
