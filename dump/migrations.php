<?php

use App\Factory;
use Dotenv\Dotenv;
use Utils\Logger;

require dirname(__DIR__) . '/vendor/autoload.php';

$baseDir = __DIR__;
$migrationsDir = "$baseDir/migrations";

Dotenv::createImmutable(dirname(__DIR__))->load();

$pdo = Factory::pdo();

$scannedDir = scandir($migrationsDir, SCANDIR_SORT_ASCENDING);
$migrations = array_filter($scannedDir, function ($entry) use ($baseDir) {
  return !is_dir("$baseDir/$entry");
});

Logger::debug("starting migrations in directory $scannedDir");

foreach ($migrations as $migration) {
  $query = file_get_contents("$migrationsDir/$migration");

  if ($pdo->exec($query)) {
    Logger::info("OK: $migration");
  } else {
    Logger::error("FAILED: $migration");
  }
}
