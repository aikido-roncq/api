<?php

namespace Tests;

class UsersControllerTest extends ControllerTest
{
  // ========================================================================
  // POST /login
  // ========================================================================
  public function testLoginInvalidCredentials()
  {
    // Arrange
    $this->expectExceptionCode(401);

    $InvalidCredentials = base64_encode('invalid:credentials');

    // Act
    self::newClient()->post('/login', [
      'headers' => [
        'Authorization' => "Basic $InvalidCredentials"
      ]
    ]);
  }

  public function testLoginWhenAlreadyLoggedIn()
  {
    // Act
    $login1 = $this->login();
    $login2 = $this->login();

    // Assert
    $this->assertEquals(200, $login1->getStatusCode());
    $this->assertEquals(200, $login2->getStatusCode());
  }

  public function testLoginSuccessful()
  {
    // Act
    $res = $this->login();
    $body = $this->getBody($res);

    // Assert
    $this->assertEquals(200, $res->getStatusCode());
    $this->assertArrayHasKey('token', $body);
    $this->assertIsString($body['token']);
  }

  // ========================================================================
  // POST /logout
  // ========================================================================

  public function testLogoutSuccessful()
  {
    // Arrange
    $res = $this->login();
    $token = $this->getBody($res)['token'];

    // Act
    $res = $this->client->post('/logout', [
      'headers' => [
        'Authorization' => "Bearer $token"
      ]
    ]);

    // Assert
    $this->assertEquals(205, $res->getStatusCode());
  }

  public function testLogoutWhenNotLoggedIn()
  {
    // Arrange
    $this->expectExceptionCode(400);

    // Act
    $this->client->post('/logout');
  }

  // ========================================================================
  // POST /contact
  // ========================================================================
  public function testContactWithMissingInformation()
  {
    // Arrange
    $this->expectExceptionCode(400);

    // Act
    $this->client->post('/contact', [
      'json' => [
        'name' => 'John'
      ],
    ]);
  }

  public function testContactWithInvalidInformation()
  {
    // Arrange
    $this->expectExceptionCode(400);

    // Act
    $this->client->post('/contact', [
      'json' => [
        'name' => 'John',
        'email' => 'wrong.email',
        'content' => 'empty'
      ]
    ]);
  }

  public function testContactSuccessfull()
  {
    // Act
    $res = $this->client->post('/contact', [
      'form_params' => [
        'name' => 'John',
        'email' => 'john.doe@gmail.com',
        'content' => 'Hello, this is a sample content'
      ]
    ]);

    // Assert
    $this->assertEquals(200, $res->getStatusCode());
  }
}
