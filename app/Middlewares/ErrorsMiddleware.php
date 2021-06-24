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
use Utils\Http;
use Utils\Logger;

/**
 * Middleware to handle errors
 */
class ErrorsMiddleware
{
  /**
   * Try to proceed the request and handle potential errors
   * 
   * @param Request $req the request
   * @param RequestHandler $handler the request handler
   * @return Response the final response
   */
  public function __invoke(Request $req, RequestHandler $handler): Response
  {
    try {
      return $handler->handle($req);
    } catch (HttpNotFoundException | HttpMethodNotAllowedException $e) {
      Logger::error("resource not found: {$e->getMessage()}");
      return (new Response())->withStatus(Http::NOT_FOUND);
    } catch (ValidationException $e) {
      Logger::error("data validation failed: {$e->getMessage()}");
      return self::handle($e, $e->getCode(), $e->getErrors());
    } catch (HttpException $e) {
      Logger::error("http exception: {$e->getMessage()} ({$e->getCode()})");
      return self::handle($e, $e->getCode());
    } catch (Exception $e) {
      Logger::error("unexpected exception: $e");
      return self::handle($e, Http::INTERNAL_SERVER_ERROR);
    }
  }

  /**
   * Handle the exception passed as first parameter
   * 
   * @param Exception $e the exception to handle
   * @param int $code the exception code to send
   * @param array $errors additional errors, especially on input fields
   * @throws Exception on body writing failure
   */
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
