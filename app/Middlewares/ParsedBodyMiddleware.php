<?php

namespace App\Middlewares;

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class ParsedBodyMiddleware
{
  public function __invoke(Request $req, RequestHandler $handler): Response
  {
    if ($req->getParsedBody() === null)
      $req = $req->withParsedBody([]);

    return $handler->handle($req);
  }
}
