<?php

namespace App\Middlewares;

use App\Exceptions\LoggedOutException;
use App\Models\Connections;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Psr7\Request;
use Utils\Http;

class AuthMiddleware
{
  public function __invoke(Request $req, RequestHandler $handler): Response
  {
    if (self::isLoggedIn($req))
      return $handler->handle($req);

    $res = new Response();

    return $res
      ->withHeader('WWW-Authenticate', 'Basic realm="Dashboard"')
      ->withStatus(Http::UNAUTHORIZED);
  }

  /**
   * Check whether or not the user is logged in.
   * 
   * @param $req the request
   * @return bool true if the user is logged in
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
   * @param $req the request
   * @return string the token
   * @throws LoggedOutException if the token does not exist
   */
  public static function getToken(Request $req): string
  {
    $authorization = $req->getHeaderLine('Authorization');

    if (!preg_match('/Bearer (.+)/', $authorization, $matches)) {
      throw new LoggedOutException('No token was provided', 400);
    }

    return $matches[1];
  }
}
