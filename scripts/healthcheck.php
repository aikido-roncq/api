<?php

require dirname(__DIR__) . '/vendor/autoload.php';

$client = new \GuzzleHttp\Client(['base_uri' => 'http://localhost:80']);

try {
  $client->get('/health');
} catch (Exception $e) {
  echo 'Healthcheck failed: ' . $e->getMessage();
  exit(1);
}
