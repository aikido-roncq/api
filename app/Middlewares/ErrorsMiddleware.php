<?php

namespace App\Middlewares;

use Exception;
use App\Exceptions\HttpException;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class ErrorsMiddleware
{
  public function __invoke(Request $req, RequestHandler $handler): Response
  {
    try {
      return $handler->handle($req);
    } catch (HttpNotFoundException | HttpMethodNotAllowedException $e) {
      return (new Response())->withStatus(404);
    } catch (HttpException $e) {
      return self::handle($e, $e->getCode());
    } catch (Exception $e) {
      error_log($e);
      return self::handle($e, 500);
    }
  }

  private static function handle(Exception $e, int $code)
  {
    $res = new Response();
    $res->getBody()->write(json_encode(['message' => $e->getMessage()]));
    return $res->withHeader('Content-Type', 'application/json')->withStatus($code);
  }
}
