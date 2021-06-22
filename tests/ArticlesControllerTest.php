<?php

namespace Tests;

use Cocur\Slugify\Slugify;
use Exception;

class ArticlesControllerTest extends ControllerTest
{
  protected const BASE_URI = '/articles';
  protected const KEYS = ['date', 'slug', 'title', 'content'];
  protected const PK = 'id';

  // ========================================================================
  // GET /
  // ========================================================================

  public function testGetAll()
  {
    [$code, $articles] = $this->get(self::BASE_URI);

    $this->assertEquals(200, $code);
    $this->assertIsArray($articles);

    foreach ($articles as $article)
      $this->verifyKeys($article);
  }

  public function testArticlesAreSorted()
  {
    [, $articles] = $this->get(self::BASE_URI);

    $dates = array_map(function ($article) {
      return $article['date'];
    }, $articles);

    $sorted_dates = $dates;
    rsort($sorted_dates);

    $this->assertEquals($sorted_dates, $dates);
  }


  // ========================================================================
  // GET /:id
  // ========================================================================

  public function testFindById()
  {
    $firstId = $this->first();
    [$code, $body] = $this->get(self::BASE_URI . '/' . $firstId);
    $this->assertEquals(200, $code);
    $this->assertIsArray($body);
    $this->verifyKeys($body);
  }

  public function testFindByIdDoesntExist()
  {
    $this->expectExceptionCode(404);
    $this->get(self::BASE_URI . "/999999");
  }

  // ========================================================================
  // POST /
  // ========================================================================

  public function testPostWithoutLogin()
  {
    $this->expectExceptionCode(401);
    $this->client->post(self::BASE_URI, [
      'json' => [
        'title' => 'Sample title',
        'content' => 'Sample content'
      ]
    ]);
  }

  public function testPostSuccessfull()
  {
    $res = $this->login();
    $token = $this->getBody($res)['token'];

    $res = $this->client->post(self::BASE_URI, [
      'headers' => [
        'Authorization' => "Bearer $token"
      ],
      'json' => [
        'title' => 'Sample title',
        'content' => 'Sample content'
      ]
    ]);

    $body = self::getBody($res);

    $this->assertEquals(201, $res->getStatusCode());
    $this->verifyKeys($body);
  }

  public function testPostWithExistingTitleShouldSuccess()
  {
    $res = $this->login();
    $token = $this->getBody($res)['token'];
    $existingTitle = $this->first('title');

    $res = $this->client->post(self::BASE_URI, [
      'headers' => [
        'Authorization' => "Bearer $token"
      ],
      'json' => [
        'title' => $existingTitle,
        'content' => 'Sample content'
      ]
    ]);

    $body = self::getBody($res);

    $this->assertEquals(201, $res->getStatusCode());
    $this->verifyKeys($body);
  }

  // ========================================================================
  // DELETE /:id
  // ========================================================================

  public function testDeleteWhenNotLoggedIn()
  {
    $firstId = $this->first();

    try {
      $this->client->delete(self::BASE_URI . "/$firstId");
      throw new Exception('Should throw 401 exception', 0);
    } catch (Exception $e) {
      $this->assertEquals(401, $e->getCode());
    }

    // the article still exists
    $res = $this->client->get(self::BASE_URI . "/$firstId");
    $this->assertEquals(200, $res->getStatusCode());
  }

  public function testDeleteThatDoesntExist()
  {
    $res = $this->login();
    $token = $this->getBody($res)['token'];

    try {
      $this->client->delete(self::BASE_URI . "/99999", [
        'headers' => [
          'Authorization' => "Bearer $token"
        ],
      ]);
      throw new Exception('Should throw 404 exception', 0);
    } catch (Exception $e) {
      $this->assertEquals(404, $e->getCode());
    }
  }

  public function testDeletedSuccessfully()
  {
    $res = $this->login();
    $token = $this->getBody($res)['token'];
    $firstId = $this->first();
    $res = $this->client->delete(self::BASE_URI . "/$firstId", [
      'headers' => [
        'Authorization' => "Bearer $token"
      ],
    ]);
    $body = self::getBody($res);

    $this->assertEquals(200, $res->getStatusCode());
    $this->verifyKeys($body);

    [, $articles] = $this->get(self::BASE_URI);

    foreach ($articles as $article)
      if ($article['id'] == $firstId)
        $this->fail('Article was not deleted');
  }

  // ========================================================================
  // PUT /:id
  // ========================================================================

  public function testEditWhenNotLoggedIn()
  {
    $this->expectExceptionCode(401);
    $firstId = $this->first();

    $this->client->put(self::BASE_URI . "/$firstId", [
      'json' => [
        'title' => 'My new title'
      ]
    ]);
  }

  public function testEditNonExistent()
  {
    $this->expectExceptionCode(404);
    $res = $this->login();
    $token = $this->getBody($res)['token'];
    $this->client->put(self::BASE_URI . "/99999", [
      'headers' => [
        'Authorization' => "Bearer $token"
      ],
      'json' => [
        'title' => 'My new title'
      ]
    ]);
  }

  public function testEditSuccessfull()
  {
    $res = $this->login();
    $token = $this->getBody($res)['token'];
    $firstId = $this->first();
    $randomTitle = self::randomStr();
    [, $oldArticle] = $this->get(self::BASE_URI . "/$firstId");
    $res = $this->client->put(self::BASE_URI . "/$firstId", [
      'headers' => [
        'Authorization' => "Bearer $token"
      ],
      'json' => [
        'title' => $randomTitle
      ]
    ]);

    $newArticle = self::getBody($res);
    $newSlug = (new Slugify)->slugify($randomTitle);

    $this->assertEquals(200, $res->getStatusCode());
    $this->verifyKeys($newArticle);
    $this->assertTrue(strpos($newArticle['slug'], $newSlug) == 0);
    $this->assertEquals($randomTitle, $newArticle['title']);
    $this->assertNotEquals($oldArticle['slug'], $newArticle['slug']);
    $this->assertEquals($oldArticle['content'], $newArticle['content']);
    $this->assertEquals($oldArticle['date'], $newArticle['date']);
    $this->assertEquals($oldArticle['id'], $newArticle['id']);
  }

  public function testEditSuccessfullWithExtraKeys()
  {
    $res = $this->login();
    $token = $this->getBody($res)['token'];
    $firstId = $this->first();
    $res = $this->client->put(self::BASE_URI . "/$firstId", [
      'headers' => [
        'Authorization' => "Bearer $token"
      ],
      'json' => [
        'title' => 'Some title',
        'field0' => 'test',
        'field1' => 'somedata'
      ]
    ]);

    $body = self::getBody($res);

    $this->assertEquals(200, $res->getStatusCode());
    $this->assertArrayNotHasKey('field0', $body);
    $this->assertArrayNotHasKey('field1', $body);
  }
}
