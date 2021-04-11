<?php

namespace App\Exceptions;

use Utils\Http;

class NotFoundException extends HttpException
{
  protected $code = Http::NOT_FOUND;
  protected $message = 'Non trouvé.';
}
