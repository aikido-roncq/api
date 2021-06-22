<?php

namespace Tests;

class CorsControllerTest extends ControllerTest
{
  private const SAMPLE_ROUTES = ['/events', '/contact', '/articles/123'];

  public function testOptionsReturns200()
  {
    foreach (self::SAMPLE_ROUTES as $route) {
      $res = $this->client->request('OPTIONS', $route, [
        'headers' => [
          'Origin' => 'http://localhost:8000'
        ]
      ]);
      $headers = $res->getHeaders();
      $this->assertEquals(200, $res->getStatusCode());
      $this->assertArrayHasKey('Access-Control-Allow-Origin', $headers);
      $this->assertArrayHasKey('Access-Control-Allow-Headers', $headers);
      $this->assertArrayHasKey('Access-Control-Allow-Methods', $headers);
    }
  }
}
