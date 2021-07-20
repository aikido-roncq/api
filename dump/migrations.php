<?php

use App\Factory;
use Dotenv\Dotenv;
use Utils\Logger;

require dirname(__DIR__) . '/vendor/autoload.php';

$baseDir = __DIR__;
$migrationsDir = "$baseDir/migrations";

Dotenv::createImmutable(dirname(__DIR__))->load();

$pdo = Factory::pdo();

function get_migrations_history(): array
{
  global $pdo;

  $stmt = $pdo->prepare("SELECT script FROM migrations ORDER BY script");
  $stmt->execute();
  $scripts = $stmt->fetchAll(PDO::FETCH_COLUMN);

  return $scripts;
}

function scan_migrations(): array
{
  global $migrationsDir;
  $migrations = array_map(function ($file) {
    return basename($file);
  }, glob("$migrationsDir/*.sql"));
  return $migrations;
}

function run_migrations(array $migrations): void
{
  global $pdo;
  global $migrationsDir;

  if (empty($migrations)) {
    Logger::info("No migrations to run.");
    return;
  }

  Logger::debug("Starting migrations of directory: $migrationsDir");

  foreach ($migrations as $migration) {
    $query = file_get_contents("$migrationsDir/$migration");

    try {
      $pdo->exec($query);
      save_migration($migration);
      Logger::info("OK: $migration");
    } catch (PDOException $e) {
      Logger::error("Error running migration: $migration ({$e->getMessage()}");
    }
  }
}

function save_migration(string $migration)
{
  global $pdo;

  $stmt = $pdo->prepare("INSERT INTO migrations (script) VALUES (?)");
  $stmt->execute([$migration]);
}


$migrationsHistory = get_migrations_history();
$scannedMigrations = scan_migrations();
$migrationsToRun = array_diff($scannedMigrations, $migrationsHistory);
run_migrations($migrationsToRun);
