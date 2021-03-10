<?php

namespace App\Controllers;

use App\Models\Articles;
use App\Attributes\Route;
use App\Exceptions\ValidationException;
use App\Utils;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

#[Route('/articles')]
class ArticlesController extends Controller
{
  #[Route('[/]', 'GET')]
  public function all(Request $req, Response $res)
  {
    $articles = Articles::orderBy('date', 'desc');
    return self::send($res, $articles);
  }

  #[Route('/{slug}', 'GET')]
  public function find(Request $req, Response $res, array $args)
  {
    $article = Articles::find($args['slug']);
    return self::send($res, $article);
  }

  #[Route('[/]', 'POST', admin: true)]
  public function add(Request $req, Response $res)
  {
    $data = $req->getParsedBody() ?? [];

    try {
      $article = Articles::create($data);
    } catch (ValidationException $e) {
      return self::badRequest($res, $e->getErrors());
    }

    return self::send($res, $article, 201);
  }

  #[Route('/{slug}', 'PATCH', admin: true)]
  public function edit(Request $req, Response $res, array $args)
  {
    $data = $req->getParsedBody() ?? [];

    $data = Utils::filterKeys($data, ['title', 'content']);

    try {
      $article = Articles::update($args['slug'], array_filter($data));
    } catch (ValidationException $e) {
      return self::badRequest($res, $e->getErrors());
    }

    return self::send($res, $article);
  }

  #[Route('/{slug}', 'DELETE', admin: true)]
  public function delete(Request $req, Response $res, array $args)
  {
    $article = Articles::delete($args['slug']);
    return self::send($res, $article);
  }
}
