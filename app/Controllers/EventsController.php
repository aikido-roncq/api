<?php

namespace App\Controllers;

use App\Attributes\Route;
use App\Models\Events;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Utils\Http;

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
    $data = $req->getParsedBody();
    $event = Events::insert($data);
    return self::send($res, $event, Http::CREATED);
  }

  #[Route('/{id}', 'PUT', admin: true)]
  public function edit(Request $req, Response $res, array $args)
  {
    $data = $req->getParsedBody();
    $event = Events::update($args['id'], $data);
    return self::send($res, $event);
  }

  #[Route('/{id}', 'DELETE', admin: true)]
  public function delete(Request $req, Response $res, array $args)
  {
    $event = Events::delete($args['id']);
    return self::send($res, $event);
  }
}
