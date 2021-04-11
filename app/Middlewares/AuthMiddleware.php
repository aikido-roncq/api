<?php

namespace App\Middlewares;

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

    $res->getBody()->write(json_encode([
      'message' => "Vous n'êtes pas connecté"
    ]));

    return $res
      ->withHeader('WWW-Authenticate', 'Basic realm="Dashboard"')
      ->withStatus(Http::UNAUTHORIZED);
  }

  public static function isLoggedIn(Request $req)
  {
    $cookies = $req->getCookieParams();

    if (!array_key_exists('token', $cookies))
      return false;

    return Connections::isValid($cookies['token']);
  }
}
