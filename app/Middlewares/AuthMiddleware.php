<?php

namespace App\Middlewares;

use App\Exceptions\LoggedOutException;
use App\Models\Connections;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Psr7\Request;
use Utils\Http;
use Utils\Logger;

/**
 * Middleware to handle authentication
 */
class AuthMiddleware
{
  /**
   * Verify that the user is logged in. If so, process the request. If not,
   * return a 401 (unauthorized) response error.
   * 
   * @param Request $req the request
   * @param RequestHandler $handler the request handler
   * @return Response the final response
   */
  public function __invoke(Request $req, RequestHandler $handler): Response
  {
    if (!self::isLoggedIn($req)) {
      $res = new Response();
      Logger::error('not logged in');

      return $res
        ->withHeader('WWW-Authenticate', 'Basic realm="Dashboard"')
        ->withStatus(Http::UNAUTHORIZED);
    }

    return $handler->handle($req);
  }

  /**
   * Check whether or not the user is logged in.
   * 
   * @param Request $req the request
   * @return bool true if the user is logged in
   * @throws PDOException on PDO error
   */
  public static function isLoggedIn(Request $req): bool
  {
    try {
      $token = self::getToken($req);
    } catch (LoggedOutException $e) {
      return false;
    }

    return Connections::isValid($token);
  }

  /**
   * Get the token from the request.
   * 
   * @param Request $req the request
   * @return string the token
   * @throws LoggedOutException if the token does not exist
   */
  public static function getToken(Request $req): string
  {
    $authorization = $req->getHeaderLine('Authorization');

    if (!preg_match('/Bearer (.+)/', $authorization, $matches)) {
      throw new LoggedOutException('no token was provided', 400);
    }

    return $matches[1];
  }
}
