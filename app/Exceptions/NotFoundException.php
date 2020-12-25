<?php

namespace App\Exceptions;

use Exception;

class NotFoundException extends Exception
{
    protected $message = 'Non trouvé.';
    protected $code = 404;
}
