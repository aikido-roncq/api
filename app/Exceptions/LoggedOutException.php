<?php

namespace App\Exceptions;

class LoggedOutException extends HttpException
{
  protected $code = 401;
  protected $message = "Vous n'êtes pas connecté.";
}
