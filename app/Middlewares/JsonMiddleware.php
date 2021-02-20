<?php

namespace App\Middlewares;

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Request;

class JsonMiddleware
{
  public function __invoke(Request $req, RequestHandler $handler)
  {
    $res = $handler->handle($req);
    return $res->withHeader('Content-Type', 'application/json; charset=UTF-8');
  }
}
