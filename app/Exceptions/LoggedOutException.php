<?php

namespace App\Exceptions;

use Exception;

class LoggedOutException extends Exception
{
    protected $code = 401;
    protected $message = "Vous n'êtes pas connecté.";
}
