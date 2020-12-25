<?php

namespace App\Exceptions;

use Exception;

class UnknownException extends Exception
{
    protected $message = 'Une erreur inconnue est survenue. Veuillez réessayer plus tard.';
    protected $code = 500;
}
