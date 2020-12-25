<?php

namespace Tests;

class UsersControllerTest extends ControllerTest
{
    // ========================================================================
    // POST /login
    // ========================================================================
    public function testLoginInvalidCredentials()
    {
        $this->expectExceptionCode(401);
        $InvalidCredentials = base64_encode('invalid:credentials');

        self::newClient()->post('/login', [
            'headers' => [
                'Authorization' => "Basic $InvalidCredentials"
            ]
        ]);
    }

    public function testLoginWhenAlreadyLoggedIn()
    {
        $this->login();
        $this->assertEquals(200, $this->login()->getStatusCode());
    }

    public function testLoginSuccessful()
    {
        $res = $this->login();
        $body = self::getBody($res);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertTrue($res->hasHeader('Set-Cookie'));
        $this->assertArrayHasKey('message', $body);
    }

    // ========================================================================
    // POST /logout
    // ========================================================================

    public function testLogoutSuccessful()
    {
        $res = $this->client->post('/logout');
        $this->assertEquals(205, $res->getStatusCode());
    }

    public function testCannotPostAfterLogout()
    {
        $this->login();
        $this->client->post('/logout');
        $this->expectExceptionCode(401);
        $this->postAnArticle();
    }

    public function testLogoutWhenNotLoggedIn()
    {
        $res = $this->client->post('/logout');
        $this->assertEquals(205, $res->getStatusCode());
    }

    // ========================================================================
    // POST /contact
    // ========================================================================
    public function testContactWithMissingInformation()
    {
        $this->expectExceptionCode(400);
        $this->client->post('/contact', [
            'form_data' => [
                'name' => 'John'
            ]
        ]);
    }

    public function testContactWithInvalidInformation()
    {
        $this->expectExceptionCode(400);
        $this->client->post('/contact', [
            'form_data' => [
                'name' => 'John',
                'email' => 'wrong.email',
                'content' => 'empty'
            ]
        ]);
    }

    public function testContactSuccessfull()
    {
        $res = $this->client->post('/contact', [
            'form_params' => [
                'name' => 'John',
                'email' => 'john.doe@gmail.com',
                'content' => 'Hello, this is a sample content'
            ]
        ]);

        $this->assertEquals(200, $res->getStatusCode());
    }

    /* --------------------------------------------------------------------- */

    private function postAnArticle()
    {
        $title = $this->randomStr();
        $content = $this->randomStr();
        return $this->client->post('/articles', [
            'form_params' => compact('title', 'content')
        ]);
    }
}
