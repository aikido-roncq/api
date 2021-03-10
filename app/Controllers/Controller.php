<?php

namespace App\Controllers;

use App\App;
use App\Exceptions\LoggedOutException;
use App\Models\Connections;
use Exception;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class Controller
{
  private static function json(Response $res, $data)
  {
    $res->getBody()->write(json_encode($data));
    return $res;
  }

  protected static function send(Response $res, $data, int $code = 200)
  {
    return self::json($res, $data)
      ->withStatus($code);
  }

  protected static function badRequest(Response $res, array $errors)
  {
    return self::json($res, $errors)
      ->withStatus(400);
  }

  protected static function isLoggedIn(Request $req)
  {
    try {
      $token = self::extractToken($req);
    } catch (LoggedOutException $e) {
      return false;
    }

    return Connections::isValid($token);
  }

  protected static function extractToken(Request $req)
  {
    $cookies = $req->getCookieParams();

    if (array_key_exists('token', $cookies))
      return $cookies['token'];

    throw new LoggedOutException();
  }

  protected static function getView(string $view, array $args)
  {
    extract($args);
    ob_start();
    require sprintf('%s/%s.php', App::VIEWS_PATH, $view);
    return ob_get_clean();
  }
}
