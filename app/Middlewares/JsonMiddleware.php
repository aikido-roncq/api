<?php

namespace App\Middlewares;

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * Middleware to add JSON headers to the response
 */
class JsonMiddleware
{
  /**
   * Set the Content-Type of the response to JSON 
   * 
   * @param Request $req the request
   * @param RequestHandler $handler the request handler
   * @return Response the final response
   */
  public function __invoke(Request $req, RequestHandler $handler): Response
  {
    $res = $handler->handle($req);
    return $res->withHeader('Content-Type', 'application/json; charset=UTF-8');
  }
}
