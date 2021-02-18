<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
  private string $path;
  private string $method;

  public function __construct(string $path, string $method)
  {
    $this->path = $path;
    $this->method = $method;
  }

  public function getPath()
  {
    return $this->path;
  }

  public function getMethod()
  {
    return $this->method;
  }
}
