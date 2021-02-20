<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Route
{
  private string $path;
  private string $method;
  private bool $admin;

  public function __construct(string $path, string $method = '', bool $admin = false)
  {
    $this->path = $path;
    $this->method = $method;
    $this->admin = $admin;
  }

  public function getPath()
  {
    return $this->path;
  }

  public function getMethod()
  {
    return $this->method;
  }

  public function isAdmin()
  {
    return $this->admin;
  }
}
