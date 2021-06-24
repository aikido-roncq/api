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

/**
 * A model, which correspond to a table in the database
 */
abstract class Model
{
  /**
   * The primary key
   */
  protected static string $pk;

  /**
   * Table keys
   */
  protected static array $keys;

  /**
   * Validation rules
   */
  protected static array $rules;

  /**
   * Fields labels
   */
  protected static array $labels;

  /**
   * Create a new instance from input data
   * 
   * @param array $fields input fields
   * @throws ValidationException the any field does not verify the rules
   */
  public function __construct(array $fields = [])
  {
    $allowedFields = Arrays::filterKeys($fields, static::$keys);
    $valitron = Validation::validate($allowedFields, static::$rules, static::$labels);

    if (!$valitron->validate())
      throw new ValidationException('invalid data for model', $valitron->errors());

    foreach ($allowedFields as $key => $value)
      $this->$key = $value;
  }

  /**
   * Create a new instance from input data
   */
  protected abstract static function make(array $fields = []): self;

  /**
   * Get a new query builder instance
   * 
   * @return QueryBuilder a new query builder instance
   */
  private static function builder(): QueryBuilder
  {
    return new QueryBuilder(Factory::pdo());
  }

  /**
   * Get the table name of the current model
   * 
   * @return string the table name
   */
  private static function table(): string
  {
    $className = explode('\\', static::class);
    return strtolower(end($className));
  }

  /**
   * Find a row from the primary key
   * 
   * @param string $key the key to search for
   * @return self an instance of the model that was found
   * @throws PDOException on PDO error
   * @throws NotFoundException if no such row was found
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
      throw new NotFoundException("model with key '$key' not found");

    return new static($row);
  }

  /**
   * Get all the rows for the corresponding table
   * 
   * @param array $conditions the conditions
   * @return array a list of model instances for the rows
   * @throws PDOException on PDO error
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
   * Find all the rows for the model and order them by the given key
   * 
   * @param string $key the key used for the order
   * @param string $order asc or desc
   * @param array $conds additionnal conditions
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
   * Insert a new row in the table of the corresponding model
   * 
   * @param array $fields the row fields
   * @return self the newly inserted row as a model instance
   * @throws ValidationException on data error
   * @throws PDOException on PDO error
   * @throws UnknownException on unknown error
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
      throw new UnknownException('create failed');

    $pk = $values[static::$pk] ?? self::builder()->lastInsertId();

    return self::find($pk);
  }

  /**
   * Delete a row from the given key
   * 
   * @param string $key the key of the row to delete
   * @return self the deleted row as a model instance
   * @throws NotFoundException if no such row was found
   * @throws PDOException on PDO error
   * @throws UnknownException on unknown error
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
      throw new UnknownException('delete failed');

    return $entry;
  }

  /**
   * Update the row having the given key
   * 
   * @param string $key the key of the row to update
   * @param array $new_fields the fields to update
   * @return self the updated row as a model instance
   * @throws NotFoundException if no such row was found
   * @throws ValidationException on data error
   * @throws PDOException on PDO error
   * @throws UnknownException on unknown error
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
      throw new UnknownException('update failed');

    return $new_instance;
  }
}
