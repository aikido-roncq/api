<?php

namespace Tests;

use Cocur\Slugify\Slugify;
use Exception;

class ArticlesControllerTest extends ControllerTest
{
    protected const BASE_URI = '/articles';
    protected const KEYS = ['date', 'slug', 'title', 'content'];
    protected const PK = 'slug';

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
    // GET /:slug
    // ========================================================================

    public function testFindBySlug()
    {
        $firstSlug = $this->first();
        [$code, $body] = $this->get(self::BASE_URI . "/$firstSlug");
        $this->assertEquals(200, $code);
        $this->assertIsArray($body);
        $this->verifyKeys($body);
    }

    public function testFindBySlugDoesntExist()
    {
        $this->expectExceptionCode(404);
        $randomSlug = self::randomStr();
        $this->get(self::BASE_URI . "/$randomSlug");
    }

    // ========================================================================
    // POST /
    // ========================================================================

    public function testPostWithoutLogin()
    {
        $this->expectExceptionCode(401);
        $this->client->post(self::BASE_URI, [
            'form_params' => [
                'title' => 'Sample title',
                'content' => 'Sample content'
            ]
        ]);
    }

    public function testPostSuccessfull()
    {
        $this->login();

        $res = $this->client->post(self::BASE_URI, [
            'form_params' => [
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
        $this->login();
        $existingTitle = $this->first('title');

        $res = $this->client->post(self::BASE_URI, [
            'form_params' => [
                'title' => $existingTitle,
                'content' => 'Sample content'
            ]
        ]);

        $body = self::getBody($res);

        $this->assertEquals(201, $res->getStatusCode());
        $this->verifyKeys($body);
    }

    // ========================================================================
    // DELETE /:slug
    // ========================================================================

    public function testDeleteWhenNotLoggedIn()
    {
        $firstSlug = $this->first();

        try {
            $this->client->delete(self::BASE_URI . "/$firstSlug");
            throw new Exception('Should throw 401 exception', 0);
        } catch (Exception $e) {
            $this->assertEquals(401, $e->getCode());
        }

        // the article still exists
        $res = $this->client->get(self::BASE_URI . "/$firstSlug");
        $this->assertEquals(200, $res->getStatusCode());
    }

    public function testDeleteThatDoesntExist()
    {
        $this->login();

        $randomSlug = self::randomStr();

        try {
            $this->client->delete(self::BASE_URI . "/$randomSlug");
            throw new Exception('Should throw 404 exception', 0);
        } catch (Exception $e) {
            $this->assertEquals(404, $e->getCode());
        }
    }

    public function testDeletedSuccessfully()
    {
        $this->login();
        $firstSlug = $this->first();
        $res = $this->client->delete(self::BASE_URI . "/$firstSlug");
        $body = self::getBody($res);

        $this->assertEquals(200, $res->getStatusCode());
        $this->verifyKeys($body);

        [, $articles] = $this->get(self::BASE_URI);

        foreach ($articles as $article)
            if ($article['slug'] == $firstSlug)
                $this->fail('Article was not deleted');
    }

    // ========================================================================
    // PATCH /:slug
    // ========================================================================

    public function testEditWhenNotLoggedIn()
    {
        $this->expectExceptionCode(401);
        $firstSlug = $this->first();

        $this->client->patch(self::BASE_URI . "/$firstSlug", [
            'form_params' => [
                'title' => 'My new title'
            ]
        ]);
    }

    public function testEditNonExistent()
    {
        $this->expectExceptionCode(404);
        $this->login();
        $randomSlug = self::randomStr();
        $this->client->patch(self::BASE_URI . "/$randomSlug", [
            'form_params' => [
                'title' => 'My new title'
            ]
        ]);
    }

    public function testEditSuccessfull()
    {
        $this->login();
        $firstSlug = $this->first();
        $randomTitle = self::randomStr();
        [, $oldArticle] = $this->get(self::BASE_URI . "/$firstSlug");
        $res = $this->client->patch(self::BASE_URI . "/$firstSlug", [
            'form_params' => [
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
    }

    public function testEditSuccessfullWithExtraKeys()
    {
        $this->login();
        $firstSlug = $this->first();
        $res = $this->client->patch(self::BASE_URI . "/$firstSlug", [
            'form_params' => [
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
