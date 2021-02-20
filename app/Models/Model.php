<?php

namespace App\Models;

use App\Exceptions\NotFoundException;
use App\Exceptions\UnknownException;
use App\Exceptions\ValidationException;
use Ludal\QueryBuilder\QueryBuilder;
use App\Factory;
use App\Utils;

abstract class Model
{
  protected static string $pk;
  protected static array $rules;
  protected static array $labels;

  public function __construct(array $fields = [])
  {
    $allowedKeys = Utils::filterKeys($fields, static::$rules['required']);
    $valitron = Utils::validate($allowedKeys, static::$rules, static::$labels);

    if (!$valitron->validate())
      throw new ValidationException($valitron->errors());

    foreach ($allowedKeys as $key => $value)
      $this->$key = $value;
  }

  protected abstract static function make(array $fields = []);

  private static function builder()
  {
    return new QueryBuilder(Factory::pdo());
  }

  private static function table()
  {
    $className = explode('\\', static::class);
    return strtolower(end($className));
  }

  public static function find(string $key)
  {
    $row = self::builder()
      ->select()
      ->from(static::table())
      ->where(sprintf('%s = :key', static::$pk))
      ->setParam(':key', $key)
      ->fetch();

    if (!$row)
      throw new NotFoundException();

    return new static($row);
  }

  public static function all(array $conditions = [])
  {
    $rows = self::builder()
      ->select()
      ->from(static::table())
      ->where(...$conditions)
      ->fetchAll();

    return array_map(function ($row) {
      return new static($row);
    }, $rows);
  }

  public static function orderBy(string $key = null, string $order = 'asc', array $conditions = [])
  {
    if (is_null($key))
      $key = static::$pk;

    $rows = self::builder()
      ->select()
      ->from(static::table())
      ->where(...$conditions)
      ->orderBy($key, $order)
      ->fetchAll();

    return array_map(function ($row) {
      return new static($row);
    }, $rows);
  }

  public static function create(array $fields = [])
  {
    $instance = static::make($fields);
    $values = get_object_vars($instance);

    $created = self::builder()
      ->insertInto(static::table())
      ->values($values)
      ->execute();

    if (!$created)
      throw new UnknownException();

    $pk = $values[static::$pk] ?? self::builder()->lastInsertId();

    return self::find($pk);
  }

  public static function delete(string $key)
  {
    $entry = self::find($key);

    $deleted = self::builder()
      ->deleteFrom(static::table())
      ->where(sprintf('%s = :key', static::$pk))
      ->setParam(':key', $key)
      ->execute();

    if (!$deleted)
      throw new UnknownException();

    return $entry;
  }

  public static function update(string $key, array $new_fields)
  {
    $fields = get_object_vars(self::find($key));
    $updated = array_merge($fields, $new_fields);
    $new_instance = new static($updated);

    $executed = self::builder()
      ->update(static::table())
      ->set($updated)
      ->where(sprintf('%s = :key', static::$pk))
      ->setParam(':key', $key)
      ->execute();

    if (!$executed)
      throw new UnknownException();

    return $new_instance;
  }
}
