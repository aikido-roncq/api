<?php

namespace App\Controllers;

use App\App;
use RuntimeException;
use Slim\Psr7\Response;
use Utils\Http;

/**
 * Base controller
 */
abstract class Controller
{
  /**
   * Send a response with some data associated with a HTTP status code
   * 
   * @param Response $res the current built response
   * @param mixed $data the data to send
   * @param int $code (optional) the HTTP status code to send
   * @return Response the response with the given data as body
   * @throws RuntimeException on body writing failure
   */
  protected static function send(Response $res, $data, int $code = Http::OK): Response
  {
    $res->getBody()->write(json_encode($data));
    return $res->withStatus($code);
  }

  /**
   * Get the view as a string
   * 
   * @param string $view the path to the view
   * @param array $args the variables used by the view
   * @return string the view
   */
  protected static function getView(string $view, array $args): string
  {
    extract($args);
    ob_start();
    require sprintf('%s/%s.php', App::VIEWS_PATH, $view);
    return ob_get_clean();
  }
}
