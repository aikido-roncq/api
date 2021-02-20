<?php

namespace App;

use App\Attributes\Route;
use App\Controllers\ArticlesController;
use App\Controllers\CorsController;
use App\Controllers\EventsController;
use App\Controllers\GalleryController;
use App\Controllers\UsersController;
use Slim\Factory\AppFactory as SlimAppFactory;
use Valitron\Validator;
use Slim\App as SlimApp;
use Dotenv\Dotenv;
use ReflectionClass;

define('ROOT', dirname(__DIR__));

class App
{
  public const VIEWS_PATH = ROOT . '/app/Views';

  const CONTROLLERS = [
    ArticlesController::class,
    CorsController::class,
    EventsController::class,
    GalleryController::class,
    UsersController::class,
  ];

  public function __construct()
  {
    Dotenv::createImmutable(ROOT)->load();
    Validator::lang('fr');
    $app = SlimAppFactory::create();
    self::registerControllers($app);
    $app->run();
  }

  private static function registerControllers(SlimApp $app)
  {
    foreach (self::CONTROLLERS as $controller)
      self::registerController($controller, $app);
  }

  private static function registerController(string $controller, SlimApp $app)
  {
    $reflection = new ReflectionClass($controller);
    $classAttributes = $reflection->getAttributes();
    $methods = $reflection->getMethods();
    $prefix = '';

    if ($classAttributes)
      $prefix = $classAttributes[0]->newInstance()->getPath();

    foreach ($methods as $method) {
      $attributes = $method->getAttributes(Route::class);

      foreach ($attributes as $attribute) {
        $route = $attribute->newInstance();
        [$httpMethod, $path] = [$route->getMethod(), $route->getPath()];
        $handler = [new $controller(), $method->getName()];
        $app->map([$httpMethod], $prefix . $path, $handler);
      }
    }
  }
}
