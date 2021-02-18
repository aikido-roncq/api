<?php

namespace App\Controllers;

use App\Attributes\Route;

class CorsController extends Controller
{
  #[Route('/[*]', 'OPTIONS')]
  public function preflight()
  {
    self::status(200);
  }
}
