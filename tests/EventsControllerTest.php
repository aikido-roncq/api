<?php

namespace Tests;

class EventsControllerTest extends ControllerTest
{
  protected const BASE_URI = '/events';
  protected const KEYS = ['id', 'title', 'info', 'start_date', 'end_date'];
  protected const PK = 'id';

  // ========================================================================
  // GET /
  // ========================================================================

  public function testGetAll()
  {
    [$code, $events] = $this->get(self::BASE_URI);

    $this->assertEquals(200, $code);
    $this->assertIsArray($events);

    foreach ($events as $event)
      $this->verifyKeys($event);
  }

  public function testSortedByStartDate()
  {
    [, $events] = $this->get(self::BASE_URI);

    $start_dates = array_map(function ($event) {
      return $event['start_date'];
    }, $events);

    $sorted_dates = $start_dates;
    sort($sorted_dates);

    $this->assertEquals($sorted_dates, $start_dates);
  }

  // ========================================================================
  // GET /:id
  // ========================================================================
  public function testGetByIdNonExistingEvent()
  {
    $id = 483483;

    $this->expectExceptionCode(404);
    $this->get(self::BASE_URI . "/$id");
  }

  public function testGetByIdSuccessful()
  {
    $firstId = $this->first();
    [$code, $event] = $this->get(self::BASE_URI . "/$firstId");

    $this->assertEquals(200, $code);
    $this->assertIsArray($event);
    $this->assertEquals($firstId, $event['id']);
    $this->verifyKeys($event);
  }

  // ========================================================================
  // POST /
  // ========================================================================
  public function testPostWhenNotLoggedIn()
  {
    $this->expectExceptionCode(401);
    $this->client->post(self::BASE_URI);
  }

  public function testPostWhenMissingData()
  {
    $res = $this->login();
    $token = $this->getBody($res)['token'];
    $this->expectExceptionCode(400);
    $this->client->post(self::BASE_URI, [
      'headers' => [
        'Authorization' => "Bearer $token"
      ],
      'json' => [
        'title' => 'New title',
        'end_date' => '2021-09-04',
      ]
    ]);
  }

  public function testPostWithInvalidDates()
  {
    $res = $this->login();
    $token = $this->getBody($res)['token'];
    $this->expectExceptionCode(400);
    $this->client->post(self::BASE_URI, [
      'headers' => [
        'Authorization' => "Bearer $token"
      ],
      'json' => [
        'title' => 'New title',
        'info' => 'Some info',
        'start_date' => '2021-09-05',
        'end_date' => '2021-09-04',
      ]
    ]);
  }

  public function testPostSuccessfull()
  {
    $res = $this->login();
    $token = $this->getBody($res)['token'];

    $data = [
      'title' => 'New title',
      'info' => 'Sample content',
      'start_date' => '2021-09-03',
      'end_date' => '2021-09-04',
    ];

    $res = $this->client->post(self::BASE_URI, [
      'headers' => [
        'Authorization' => "Bearer $token"
      ],
      'json' => $data
    ]);

    $event = self::getBody($res);

    $this->assertEquals(201, $res->getStatusCode());
    $this->assertArrayHasKey('id', $event);
    $this->assertEquals($data + ['id' => $event['id']], $event);
  }

  // ========================================================================
  // DELETE /:id
  // ========================================================================

  public function testDeleteWhenLoggedOut()
  {
    $firstId = $this->first();

    $this->expectExceptionCode(401);
    $this->client->delete(self::BASE_URI . "/$firstId");
  }

  public function testDeleteNonExistingEvent()
  {
    $res = $this->login();
    $token = $this->getBody($res)['token'];

    $this->expectExceptionCode(404);
    $this->client->delete(self::BASE_URI . '/3498339', [
      'headers' => [
        'Authorization' => "Bearer $token"
      ],
    ]);
  }

  public function testDeleteSuccessfull()
  {
    $res = $this->login();
    $token = $this->getBody($res)['token'];
    $firstId = $this->first();
    $res = $this->client->delete(self::BASE_URI . "/$firstId", [
      'headers' => [
        'Authorization' => "Bearer $token"
      ]
    ]);

    $this->assertEquals(200, $res->getStatusCode());
    $this->assertNotEquals($this->first(), $firstId);
  }

  // ========================================================================
  // PATCH /:id
  // ========================================================================
  public function testEditWhenLoggedOut()
  {
    $firstId = $this->first();

    $this->expectExceptionCode(401);
    $this->client->patch(self::BASE_URI . "/$firstId", [
      'json' => [
        'title' => 'New title'
      ]
    ]);
  }

  public function testEditNonExistentEvent()
  {
    $res = $this->login();
    $token = $this->getBody($res)['token'];
    $id = 5838434;

    $this->expectExceptionCode(404);
    $this->client->patch(self::BASE_URI . "/$id", [
      'headers' => [
        'Authorization' => "Bearer $token"
      ],
      'json' => [
        'title' => 'New title'
      ]
    ]);
  }

  public function testEditSuccessfull()
  {
    $res = $this->login();
    $token = $this->getBody($res)['token'];
    $firstId = $this->first();

    $res = $this->client->patch(self::BASE_URI . "/$firstId", [
      'headers' => [
        'Authorization' => "Bearer $token"
      ],
      'json' => [
        'title' => 'New title'
      ]
    ]);

    $event = self::getBody($res);

    $this->assertEquals(200, $res->getStatusCode());
    $this->assertEquals('New title', $event['title']);

    [, $editedEvent] = $this->get(self::BASE_URI . "/$firstId");

    $this->assertEquals('New title', $editedEvent['title']);
  }
}
