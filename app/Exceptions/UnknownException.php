<?php

namespace App\Exceptions;

class UnknownException extends HttpException
{
  protected $code = 500;
  protected $message = 'Une erreur inconnue est survenue.';
}
