<?php

namespace App\Middlewares;

use Exception;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class ErrorsMiddleware
{
  public function __invoke(Request $req, RequestHandlerInterface $handler): Response
  {
    try {
      return $handler->handle($req);
    } catch (HttpNotFoundException | HttpMethodNotAllowedException $e) {
      return (new Response())->withStatus(404);
    } catch (Exception $e) {
      error_log($e->getMessage());
      return (new Response())->withStatus(500);
    }
  }
}
