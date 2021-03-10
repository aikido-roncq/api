<?php

namespace App\Exceptions;

class NotFoundException extends HttpException
{
  protected $code = 404;
  protected $message = 'Non trouvé.';
}
