<?php

namespace App\Controllers;

use App\Attributes\Route;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Utils\Http;

/**
 * Controller to check application health
 */
class HealthController extends Controller
{
  /**
   * Check application health
   * 
   * @param Request $req the request
   * @param Response $res the current response
   * @return Response the final response
   */
  #[Route('/health', 'GET')]
  public function health(Request $req, Response $res)
  {
    return $res->withStatus(Http::OK);
  }
}
