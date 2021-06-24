<?php

namespace App\Controllers;

use App\Attributes\Route;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnknownException;
use App\Exceptions\ValidationException;
use App\Models\Events;
use RuntimeException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Utils\Http;

/**
 * Events controller
 */
#[Route('/events')]
class EventsController extends Controller
{
  /**
   * Get the list of all upcoming events
   * 
   * @param Request $req the request
   * @param Response $res the current response
   * @return Response the response with the list of events
   * @throws PDOException if any PDO error occurs
   * @throws RuntimeException on body writing failure
   */
  #[Route('[/]', 'GET')]
  public function all(Request $req, Response $res): Response
  {
    $events = Events::orderBy('start_date');
    return self::send($res, $events);
  }

  /**
   * Find an event from its id
   * 
   * @param Request $req the request
   * @param Response $res the current response
   * @param array $args contains the event id
   * @return Response the final response containing the event
   * @throws NotFoundException if the event was not found
   * @throws RuntimeException on body writing failure
   */
  #[Route('/{id}', 'GET')]
  public function find(Request $req, Response $res, array $args): Response
  {
    $event = Events::find($args['id']);
    return self::send($res, $event);
  }

  /**
   * Post a new event
   * 
   * @param Request $req the request
   * @param Response $res the current response
   * @return Response the final response
   * @throws ValidationException if there are any data errors
   * @throws PDOException if any PDO error occurs
   * @throws UnknownException when an unkown error occurs
   * @throws RuntimeException on body writing failure
   */
  #[Route('[/]', 'POST', admin: true)]
  public function add(Request $req, Response $res): Response
  {
    $data = $req->getParsedBody();
    $event = Events::insert($data);
    return self::send($res, $event, Http::CREATED);
  }

  /**
   * Update an event from its id
   * 
   * @param Request $req the request
   * @param Response $res the current response
   * @param array $args contains the event id
   * @return Response the final response
   * @throws NotFoundException if the event was not found
   * @throws ValidationException if there are any data errors
   * @throws PDOException if any PDO error occurs
   * @throws RuntimeException on body writing failure
   */
  #[Route('/{id}', 'PUT', admin: true)]
  public function edit(Request $req, Response $res, array $args): Response
  {
    $data = $req->getParsedBody();
    $event = Events::update($args['id'], $data);
    return self::send($res, $event);
  }

  /**
   * Delete an event from its id
   * 
   * @param Request $req the request
   * @param Response $res the current response
   * @param array $args contains the event id
   * @return Response the final response
   * @throws NotFoundException if the event was not found
   * @throws ValidationException if there are any data errors
   * @throws PDOException if any PDO error occurs
   * @throws UnknownException when an unkown error occurs
   * @throws RuntimeException on body writing failure
   */
  #[Route('/{id}', 'DELETE', admin: true)]
  public function delete(Request $req, Response $res, array $args): Response
  {
    $event = Events::delete($args['id']);
    return self::send($res, $event);
  }
}
