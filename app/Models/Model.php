<?php

namespace App\Models;

use App\Exceptions\NotFoundException;
use App\Exceptions\UnknownException;
use Ludal\QueryBuilder\QueryBuilder;
use App\Factory;
use App\Utils;
use GUMP;
use InvalidArgumentException;

abstract class Model
{
    protected static $pk;
    protected static $rules;
    protected static $filters;

    public function __construct(array $fields)
    {
        foreach ($fields as $key => $value)
            $this->$key = $value;
    }

    protected abstract static function make(array $fields = []);

    protected static function validate(array $fields, array $rules, array $filters = [])
    {
        return Utils::validate($fields, $rules, $filters);
    }

    protected static function builder()
    {
        return new QueryBuilder(Factory::pdo());
    }

    protected static function table()
    {
        $className = explode('\\', static::class);
        return strtolower(end($className));
    }

    /* --------------------------------------------------------------------- */

    public static function find($key)
    {
        $row = self::builder()
            ->select()
            ->from(static::table())
            ->where(static::$pk . " = :key")
            ->setParam(':key', $key)
            ->fetch();

        if (!$row)
            throw new NotFoundException();

        return new static($row);
    }

    public static function all($conditions = [])
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
        $entity = static::make($fields);
        $values = get_object_vars($entity);

        $created = self::builder()
            ->insertInto(static::table())
            ->values($values)
            ->execute();

        if (!$created)
            throw new UnknownException();

        $pk = $values[static::$pk] ?? self::builder()->lastInsertId();

        $entry = self::builder()
            ->select()
            ->from(static::table())
            ->where(sprintf('%s = :key', static::$pk))
            ->setParam(':key', $pk)
            ->fetch();

        return new static($entry);
    }

    public static function delete($key)
    {
        $entry = self::find($key);

        self::builder()
            ->deleteFrom(static::table())
            ->where(static::$pk . ' = :key')
            ->setParam(':key', $key)
            ->execute();

        return $entry;
    }

    public static function update($key, $new_fields)
    {
        $fields = get_object_vars(self::find($key));
        $updated = array_merge($fields, $new_fields);
        $new_entry = new static($updated);

        $executed = self::builder()
            ->update(static::table())
            ->set($updated)
            ->where(static::$pk . ' = :key')
            ->setParam(':key', $key)
            ->execute();

        if (!$executed)
            throw new UnknownException();

        return $new_entry;
    }
}
