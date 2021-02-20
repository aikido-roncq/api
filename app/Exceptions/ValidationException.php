<?php

namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
  private array $errors = [];
  protected $code = 400;

  public function __construct(array $errors)
  {
    parent::__construct();
    $this->errors = $errors;
  }

  public function getErrors()
  {
    return $this->errors;
  }
}
