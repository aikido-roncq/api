<?php

namespace App\Controllers;

use App\Attributes\Route;
use App\Exceptions\ValidationException;
use App\Models\Events;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

#[Route('/events')]
class EventsController extends Controller
{
  #[Route('[/]', 'GET')]
  public function all(Request $req, Response $res)
  {
    $events = Events::orderBy('start_date');
    return self::send($res, $events);
  }

  #[Route('/{id}', 'GET')]
  public function find(Request $req, Response $res, array $args)
  {
    $event = Events::find($args['id']);
    return self::send($res, $event);
  }

  #[Route('[/]', 'POST', admin: true)]
  public function add(Request $req, Response $res)
  {
    $data = $req->getParsedBody() ?? [];

    try {
      $event = Events::create($data);
    } catch (ValidationException $e) {
      return self::badRequest($res, $e->getErrors());
    }

    return self::send($res, $event, 201);
  }

  #[Route('/{id}', 'PATCH', admin: true)]
  public function edit(Request $req, Response $res, array $args)
  {
    $data = $req->getParsedBody() ?? [];

    try {
      $event = Events::update($args['id'], $data);
    } catch (ValidationException $e) {
      return self::badRequest($res, $e->getErrors());
    }

    return self::send($res, $event);
  }

  #[Route('/{id}', 'DELETE', admin: true)]
  public function delete(Request $req, Response $res, array $args)
  {
    $event = Events::delete($args['id']);
    return self::send($res, $event);
  }
}
