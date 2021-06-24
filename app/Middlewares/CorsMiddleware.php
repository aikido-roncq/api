<?php

namespace App\Middlewares;

use Slim\Psr7\Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

/**
 * Controller that adds CORS headers
 */
class CorsMiddleware
{
  /**
   * Add CORS headers to the response.
   * 
   * @param Request $req the request
   * @param RequestHandler the request handler
   * @param Response the final response
   */
  public function __invoke(Request $req, RequestHandler $handler): Response
  {
    $res = $handler->handle($req);
    $origin = $req->getHeaderLine('origin');

    if ($origin)
      $res = $res->withHeader('Access-Control-Allow-Origin', $origin);

    return $res
      ->withHeader('Access-Control-Allow-Credentials', 'true')
      ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
      ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
  }
}
