<?php

namespace App\Exceptions;

use Exception;

class ArticleNotFoundException extends Exception
{
    protected $message = "L'article n'existe pas.";
    protected $code = 404;
}
