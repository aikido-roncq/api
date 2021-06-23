<?php

namespace Tests;

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

abstract class ControllerTest extends TestCase
{
  protected const HOST = 'http://aikido-php';
  protected const BASE_URI = '';
  protected const KEYS = [];
  protected const PK = '';

  private const CLIENT_OPTS = [
    'base_uri' => self::HOST,
    'timeout' => 5,
  ];

  /**
   * @var Client
   */
  protected $client;

  public static function setUpBeforeClass(): void
  {
    Dotenv::createImmutable(dirname(__DIR__))->load();
  }

  public function setUp(): void
  {
    $this->client = self::newClient();
  }

  protected static function newClient()
  {
    return new Client(self::CLIENT_OPTS);
  }

  protected function get(string $endpoint)
  {
    $res = $this->client->get($endpoint);
    $code = $res->getStatusCode();
    $body = self::getBody($res);
    return [$code, $body];
  }

  protected function login(): ResponseInterface
  {
    $login = $_ENV['ADMIN_USER'];
    $password = $_ENV['ADMIN_PW'];
    $credentials = base64_encode("$login:$password");
    $res = $this->client->post('/login', [
      'headers' => [
        'Authorization' => "Basic $credentials"
      ]
    ]);
    return $res;
  }

  protected static function getBody($res)
  {
    return json_decode($res->getBody()->getContents(), true);
  }

  protected static function randomStr(int $nBytes = 16)
  {
    return bin2hex(random_bytes($nBytes));
  }

  protected function verifyKeys($data)
  {
    foreach (static::KEYS as $key)
      $this->assertArrayHasKey($key, $data);
  }

  protected function first(string $key = null)
  {
    [, $entries] = $this->get(static::BASE_URI);
    $key = $key ?: static::PK;
    return $entries[0][$key];
  }
}
