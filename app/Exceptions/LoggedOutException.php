<?php

namespace App\Exceptions;

use Utils\Http;

class LoggedOutException extends HttpException
{
  protected $code = Http::UNAUTHORIZED;
  protected $message = "Vous n'êtes pas connecté.";
}
