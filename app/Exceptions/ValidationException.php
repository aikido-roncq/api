<?php

namespace App\Exceptions;

use Utils\Http;

/**
 * Exception thrown when data does not pass the validation rules
 */
class ValidationException extends HttpException
{
  private array $errors = [];

  public function __construct(string $message, array $errors)
  {
    parent::__construct($message, Http::BAD_REQUEST);
    $this->errors = $errors;
  }

  public function getErrors()
  {
    return $this->errors;
  }
}
