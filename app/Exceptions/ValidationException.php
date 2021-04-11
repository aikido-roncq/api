<?php

namespace App\Exceptions;

use Utils\Http;

class ValidationException extends HttpException
{
  protected $code = Http::BAD_REQUEST;
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
