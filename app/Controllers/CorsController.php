<?php

namespace App\Controllers;

use App\Attributes\Route;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * Controller to handle CORS requests (OPTIONS)
 */
class CorsController extends Controller
{
  /**
   * Handles any OPTIONS request
   * 
   * @param Request $req the request
   * @param Response $res the current response
   * @return Response the final response
   */
  #[Route('/{route:.*}', 'OPTIONS')]
  public function preflight(Request $req, Response $res)
  {
    return $res;
  }
}
