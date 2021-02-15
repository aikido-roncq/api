<?php

namespace App\Controllers;

class CorsController extends Controller
{
  public function preflight()
  {
    self::status(200);
  }
}
