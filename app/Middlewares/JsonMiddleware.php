<?php

namespace App\Middlewares;

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class JsonMiddleware
{
  public function __invoke(Request $req, RequestHandler $handler): Response
  {
    $res = $handler->handle($req);
    return $res->withHeader('Content-Type', 'application/json; charset=UTF-8');
  }
}
