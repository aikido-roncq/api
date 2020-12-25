<?php

namespace App\Models;

class Gallery extends Model
{
    protected static $pk = 'src';

    protected static $rules = [
        'src' => 'required',
        'caption' => 'optional'
    ];

    protected static $filters = [
        'src' => 'trim',
        'caption' => 'trim'
    ];

    public static function make(array $fields = [])
    {
        $validData = self::validate($fields, self::$rules, self::$filters);
        return new self($validData);
    }
}
