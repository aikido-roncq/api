<?php

namespace App\Middlewares;

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * Middleware to handle the request body
 */
class ParsedBodyMiddleware
{
  /**
   * Set the body to an empty list if the request has no body
   * 
   * @param Request $req the request
   * @param RequestHandler $handler the request handler
   * @return Response the final response
   */
  public function __invoke(Request $req, RequestHandler $handler): Response
  {
    if ($req->getParsedBody() === null)
      $req = $req->withParsedBody([]);

    return $handler->handle($req);
  }
}
