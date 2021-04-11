<?php

namespace App\Controllers;

use App\App;
use Slim\Psr7\Response;
use Utils\Http;

class Controller
{
  protected static function send(Response $res, $data, int $code = Http::OK): Response
  {
    $res->getBody()->write(json_encode($data));
    return $res->withStatus($code);
  }

  protected static function getView(string $view, array $args): string
  {
    extract($args);
    ob_start();
    require sprintf('%s/%s.php', App::VIEWS_PATH, $view);
    return ob_get_clean();
  }
}
