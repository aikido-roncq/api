<?php

namespace Tests;

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

abstract class ControllerTest extends TestCase
{
    protected const HOST = 'http://localhost:8000';
    protected const BASE_URI = '';
    protected const KEYS = [];
    protected const PK = '';

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
        return new Client([
            'base_uri' => self::HOST,
            'cookies' => true,
            'timeout' => 5,
            'debug' => true
        ]);
    }

    protected function get(string $endpoint)
    {
        $res = $this->client->get($endpoint);
        $code = $res->getStatusCode();
        $body = self::getBody($res);
        return [$code, $body];
    }

    protected function login()
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
