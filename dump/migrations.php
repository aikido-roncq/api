<?php

use App\Factory;
use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$baseDir = __DIR__;
$migrationsDir = "$baseDir/migrations";

Dotenv::createImmutable(dirname(__DIR__))->load();

$pdo = Factory::pdo();

$scannedDir = scandir($migrationsDir, SCANDIR_SORT_ASCENDING);
$migrations = array_filter($scannedDir, function ($entry) use ($baseDir) {
  return !is_dir("$baseDir/$entry");
});

foreach ($migrations as  $migration) {
  $query = file_get_contents("$migrationsDir/$migration");
  var_dump($pdo->exec($query));
}
