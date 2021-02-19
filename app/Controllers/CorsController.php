<?php

namespace App\Controllers;

use App\Attributes\Route;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class CorsController extends Controller
{
  #[Route('/[*]', 'OPTIONS')]
  public function preflight(Request $req, Response $res)
  {
    return $res->withStatus(200);
  }
}
