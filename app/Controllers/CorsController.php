<?php

namespace App\Controllers;

use App\Attributes\Route;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class CorsController extends Controller
{
  #[Route('/{route:.*}', 'OPTIONS')]
  public function preflight(Request $req, Response $res)
  {
    $origin = '*';
    $headers = 'X-Requested-With, Content-Type, Accept, Origin, Authorization';
    $methods = 'GET, POST, PUT, DELETE, PATCH, OPTIONS';

    return $res
      ->withHeader('Access-Control-Allow-Origin', $origin)
      ->withHeader('Access-Control-Allow-Headers', $headers)
      ->withHeader('Access-Control-Allow-Methods', $methods);
  }
}
