<?php

namespace App;

use App\Attributes\Route;
use App\Controllers\ArticlesController;
use App\Controllers\CorsController;
use App\Controllers\EventsController;
use App\Controllers\GalleryController;
use App\Controllers\HealthController;
use App\Controllers\UsersController;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\CorsMiddleware;
use App\Middlewares\ErrorsMiddleware;
use App\Middlewares\JsonMiddleware;
use App\Middlewares\ParsedBodyMiddleware;
use Slim\Factory\AppFactory as SlimAppFactory;
use Valitron\Validator;
use Slim\App as SlimApp;
use Dotenv\Dotenv;
use ReflectionClass;

define('ROOT', dirname(__DIR__));

/**
 * Application entrypoint
 */
class App
{
  /**
   * The path to the views
   */
  public const VIEWS_PATH = ROOT . '/app/Views';

  /**
   * The list of controllers
   */
  const CONTROLLERS = [
    ArticlesController::class,
    CorsController::class,
    EventsController::class,
    HealthController::class,
    GalleryController::class,
    UsersController::class,
  ];

  /**
   * The list of middlewares
   */
  const MIDDLEWARES = [
    ParsedBodyMiddleware::class,
    JsonMiddleware::class,
    ErrorsMiddleware::class,
    CorsMiddleware::class,
  ];

  /**
   * Create the application
   */
  public function __construct()
  {
    Dotenv::createImmutable(ROOT)->load();
    Validator::lang('fr');
    $app = SlimAppFactory::create();

    self::registerMiddlewares($app);
    self::registerControllers($app);

    $app->addBodyParsingMiddleware();

    header_remove('X-Powered-By');

    $app->run();
  }

  /**
   * Register the middlewares
   * 
   * @param SlimApp $app the Slim app instance
   */
  private static function registerMiddlewares(SlimApp $app)
  {
    foreach (self::MIDDLEWARES as $middleware)
      $app->add($middleware);
  }

  /**
   * Register the controllers
   * 
   * @param SlimApp $app the Slim app instance
   */
  private static function registerControllers(SlimApp $app)
  {
    foreach (self::CONTROLLERS as $controller)
      self::registerController($controller, $app);
  }

  /**
   * Register a single controller
   * 
   * @param string $controller the controller to register
   * @param SlimApp $app the Slim app instance
   */
  private static function registerController(string $controller, SlimApp $app)
  {
    $reflection = new ReflectionClass($controller);
    $classAttributes = $reflection->getAttributes();
    $methods = $reflection->getMethods();
    $prefix = null;

    if ($classAttributes)
      $prefix = $classAttributes[0]->newInstance()->getPath();

    foreach ($methods as $method) {
      $attributes = $method->getAttributes(Route::class);

      foreach ($attributes as $attribute) {
        $route = $attribute->newInstance();
        [$httpMethod, $path] = [$route->getMethod(), $route->getPath()];
        $handler = [new $controller(), $method->getName()];
        $mapped = $app->map([$httpMethod], $prefix . $path, $handler);

        if ($route->isAdmin())
          $mapped->add(AuthMiddleware::class);
      }
    }
  }
}
