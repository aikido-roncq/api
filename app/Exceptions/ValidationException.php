<?php

namespace App\Exceptions;

class ValidationException extends HttpException
{
  protected $code = 400;
  protected $message = 'Erreur lors de la validation des données.';
  private array $errors = [];

  public function __construct(array $errors)
  {
    $this->errors = $errors;
  }

  public function getErrors()
  {
    return $this->errors;
  }
}
