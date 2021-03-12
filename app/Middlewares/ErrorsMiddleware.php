<?php

namespace App\Middlewares;

use Exception;
use App\Exceptions\HttpException;
use App\Exceptions\ValidationException;
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
    } catch (ValidationException $e) {
      return self::handle($e, $e->getCode(), $e->getErrors());
    } catch (HttpException $e) {
      return self::handle($e, $e->getCode());
    } catch (Exception $e) {
      error_log($e);
      return self::handle($e, 500);
    }
  }

  private static function handle(Exception $e, int $code, array $errors = [])
  {
    $res = new Response();
    $body = ['message' => $e->getMessage()];

    if ($errors)
      $body['errors'] = $errors;

    $res->getBody()->write(json_encode($body));
    return $res->withHeader('Content-Type', 'application/json')->withStatus($code);
  }
}
