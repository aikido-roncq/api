<?php

namespace App\Controllers;

class CorsController extends Controller
{
  public function preflight()
  {
    self::headers([
      'Access-Control-Allow-Origin' => '*'
    ]);
  }
}
