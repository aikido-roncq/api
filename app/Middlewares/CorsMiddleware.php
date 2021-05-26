<?php

namespace App\Middlewares;

use Slim\Psr7\Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class CorsMiddleware
{
  public function __invoke(Request $req, RequestHandler $handler): Response
  {
    $res = $handler->handle($req);

    return $res
      ->withHeader('Access-Control-Allow-Origin', $req->getHeaderLine('origin'))
      ->withHeader('Access-Control-Allow-Credentials', 'true')
      ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
      ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
  }
}
