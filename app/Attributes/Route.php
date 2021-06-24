<?php

namespace App\Attributes;

use Attribute;

/**
 * Attribute (decorator) that defines a route. A route has a path and a HTTP method.
 * It may also be an admin route, which means it requires authentication. This attribute
 * can be used both on methods and classes. Using this attribute on a class will give
 * the base path that will be used by its methods, which are also using the attribute.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Route
{
  /**
   * The route path
   */
  private string $path;

  /**
   * The route method (GET, POST, ...)
   */
  private string $method;

  /**
   * True if the route requires authentication
   */
  private bool $admin;

  /**
   * Instantiate a new route
   * 
   * @param string $path the path of the route
   * @param string $method the HTTP method for this route
   * @param bool $admin true if the route requires authentication
   */
  public function __construct(string $path, string $method = '', bool $admin = false)
  {
    $this->path = $path;
    $this->method = $method;
    $this->admin = $admin;
  }

  /**
   * Get the path of the route
   * 
   * @return string the path
   */
  public function getPath(): string
  {
    return $this->path;
  }

  /**
   * Get the HTTP method of the route
   * 
   * @return string the method
   */
  public function getMethod()
  {
    return $this->method;
  }

  /**
   * Whether or not the route needs to be logged in
   * 
   * @return bool true if the route requires log in
   */
  public function isAdmin()
  {
    return $this->admin;
  }
}
