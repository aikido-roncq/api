<?php

namespace App\Controllers;

use App\Models\Articles;
use App\Attributes\Route;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnknownException;
use App\Exceptions\ValidationException;
use PDOException;
use RuntimeException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Utils\Arrays;
use Utils\Http;

/**
 * Articles route
 */
#[Route('/articles')]
class ArticlesController extends Controller
{
  /**
   * Get the list of all articles
   * 
   * @param Request $req the request
   * @param Response $res the current response
   * @return Response the final response
   * @throws PDOException if any PDO error occurs
   * @throws RuntimeException on body writing failure
   */
  #[Route('[/]', 'GET')]
  public function all(Request $req, Response $res): Response
  {
    $articles = Articles::orderBy('date', 'desc');
    return self::send($res, $articles);
  }

  /**
   * Get a specific article from its id
   * 
   * @param Request $req the request
   * @param Response $res the current response
   * @param array $args contains the article id
   * @return Response the final response
   * @throws NotFoundException if the article was not found
   * @throws PDOException if any PDO error occurs
   * @throws RuntimeException on body writing failure
   */
  #[Route('/{id}', 'GET')]
  public function find(Request $req, Response $res, array $args): Response
  {
    $article = Articles::find($args['id']);
    return self::send($res, $article);
  }

  /**
   * Post a new article
   * 
   * @param Request $req the request
   * @param Response $res the current response
   * @return Response the final response
   * @throws ValidationException if there are some errors in the data
   * @throws UnknownException when an unknown error occurs
   * @throws PDOException if any PDO error occurs
   * @throws RuntimeException on body writing failure
   */
  #[Route('[/]', 'POST', admin: true)]
  public function add(Request $req, Response $res): Response
  {
    $data = $req->getParsedBody();
    $article = Articles::insert($data);
    return self::send($res, $article, Http::CREATED);
  }

  /**
   * Update an article from its id
   * 
   * @param Request $req the request
   * @param Response $res the current response
   * @param array $args contains the article id
   * @return Response the final response
   * @throws ValidationException if there are some errors in the data
   * @throws NotFoundException if the article was not found
   * @throws PDOException if any PDO error occurs
   * @throws RuntimeException on body writing failure
   */
  #[Route('/{id}', 'PUT', admin: true)]
  public function edit(Request $req, Response $res, array $args): Response
  {
    $body = $req->getParsedBody();
    $data = Arrays::filterKeys($body, ['title', 'content']);
    $article = Articles::update($args['id'], array_filter($data));
    return self::send($res, $article);
  }

  /**
   * Delete an article from its id
   *
   * @param Request $req the request
   * @param Response $res the current response
   * @param array $args contains the article id
   * @return Response the final response
   * @throws NotFoundException if the article was not found
   * @throws UnknownException when an unknown error occurs
   * @throws PDOException if any PDO error occurs
   * @throws RuntimeException on body writing failure
   */
  #[Route('/{id}', 'DELETE', admin: true)]
  public function delete(Request $req, Response $res, array $args): Response
  {
    $article = Articles::delete($args['id']);
    return self::send($res, $article);
  }
}
