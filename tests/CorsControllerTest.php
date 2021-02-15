<?php

namespace Tests;

class CorsControllerTest extends ControllerTest
{
  private const SAMPLE_ROUTES = ['/events', '/contact', '/articles/123'];

  public function testOptionsReturns200()
  {
    foreach (self::SAMPLE_ROUTES as $route) {
      $r = $this->client->request('OPTIONS', $route);
      $this->assertEquals(200, $r->getStatusCode());
    }
  }
}
