<?php

namespace App\Middlewares;

use App\Models\Connections;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Request;

class AuthMiddleware
{
  public function __invoke(Request $req, RequestHandler $handler)
  {
    if (self::isLoggedIn($req))
      return $handler->handle($req);

    $res = new Response();

    $res->getBody()->write(json_encode([
      'message' => "Vous n'êtes pas connecté"
    ]));

    return $res->withStatus(401);
  }

  private static function isLoggedIn(Request $req)
  {
    $cookies = $req->getCookieParams();

    if (!array_key_exists('token', $cookies))
      return false;

    return Connections::isValid($cookies['token']);
  }
}
