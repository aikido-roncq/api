<?php

namespace App\Middlewares;

use Slim\Psr7\Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CorsMiddleware
{
  public function __invoke(Request $req, RequestHandler $handler)
  {
    $res = $handler->handle($req);

    return $res
      ->withHeader('Access-Control-Allow-Origin', '*');
  }
}
