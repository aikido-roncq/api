<?php

namespace App;

use AltoRouter;

class Router
{
    private $router;
    private $viewsPath;

    public function __construct(string $viewsPath = null)
    {
        $this->router = new AltoRouter;
        $this->viewsPath = $viewsPath;
    }

    public static function redirect(string $location, array $params = [])
    {
        if ($params) {
            $location = preg_replace('/\?.*/', '', $location);
            $location .= '?' . http_build_query($params);
        }

        header("Location: $location");
        die();
    }

    public function get(string $url, string $target, string $name = null)
    {
        $this->router->map('GET', $url, $target, $name);
        return $this;
    }

    public function post(string $url, string $target, string $name = null)
    {
        $this->router->map('POST', $url, $target, $name);
        return $this;
    }

    public function delete(string $url, string $target, string $name = null)
    {
        $this->router->map('DELETE', $url, $target, $name);
        return $this;
    }

    public function put(string $url, string $target, string $name = null)
    {
        $this->router->map('PUT', $url, $target, $name);
        return $this;
    }

    public function patch(string $url, string $target, string $name = null)
    {
        $this->router->map('PATCH', $url, $target, $name);
        return $this;
    }

    public function match(string $url, string $target, string $name = null)
    {
        $this->router->map('GET|POST', $url, $target, $name);
        return $this;
    }

    public function notFound()
    {
        http_response_code(404);
        die();
    }

    public function url(string $route, array $params = [])
    {
        return $this->router->generate($route, $params);
    }

    public function run()
    {
        $match = $this->router->match();

        if (!$match)
            $this->notFound();

        extract($match);

        [$controller, $action] = explode('#', $target);
        $controller = "\\App\\Controllers\\{$controller}Controller";
        $controller = new $controller($this->viewsPath);
        $controller->$action(...array_values($params));
    }
}
