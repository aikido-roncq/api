<?php

namespace App\Exceptions;

use Utils\Http;

class UnknownException extends HttpException
{
  protected $code = Http::INTERNAL_SERVER_ERROR;
  protected $message = 'Une erreur inconnue est survenue.';
}
