<?php

namespace App\Models;

use App\Exceptions\NotFoundException;
use App\Exceptions\UnknownException;
use App\Exceptions\ValidationException;
use Ludal\QueryBuilder\QueryBuilder;
use App\Factory;
use PDOException;
use Utils\Arrays;
use Utils\Validation;

abstract class Model
{
  protected static string $pk;
  protected static array $keys;
  protected static array $rules;
  protected static array $labels;

  /**
   * @throws ValidationException
   */
  public function __construct(array $fields = [])
  {
    $allowedFields = Arrays::filterKeys($fields, static::$keys);
    $valitron = Validation::validate($allowedFields, static::$rules, static::$labels);

    if (!$valitron->validate())
      throw new ValidationException($valitron->errors());

    foreach ($allowedFields as $key => $value)
      $this->$key = $value;
  }

  protected abstract static function make(array $fields = []): self;

  private static function builder(): QueryBuilder
  {
    return new QueryBuilder(Factory::pdo());
  }

  private static function table(): string
  {
    $className = explode('\\', static::class);
    return strtolower(end($className));
  }

  /**
   * @throws PDOException
   * @throws NotFoundException
   */
  public static function find(string $key): self
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

  /**
   * @throws PDOException
   */
  public static function all(array $conditions = []): array
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

  /**
   * @throws PDOException
   */
  public static function orderBy(string $key, string $order = 'asc', array $conds = []): array
  {
    $rows = self::builder()
      ->select()
      ->from(static::table())
      ->where(...$conds)
      ->orderBy($key, $order)
      ->fetchAll();

    return array_map(function ($row) {
      return new static($row);
    }, $rows);
  }

  /**
   * @throws ValidationException
   * @throws PDOException
   * @throws UnknownException
   */
  public static function insert(array $fields = []): self
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

  /**
   * @throws NotFoundException
   * @throws PDOException
   * @throws UnknownException
   */
  public static function delete(string $key): self
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

  /**
   * @throws NotFoundException
   * @throws ValidationException
   * @throws PDOException
   * @throws UnknownException
   */
  public static function update(string $key, array $new_fields): self
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
