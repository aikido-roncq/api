<?php

namespace App\Models;

use InvalidArgumentException;

class Events extends Model
{
    protected static $pk = 'id';

    protected static $rules = [
        'title' => 'required|max_len,50',
        'info' => 'required|max_len,250',
        'start_date' => 'required|date',
        'end_date' => 'required|date',
    ];

    protected static $filters = [
        'title' => 'trim|sanitize_string',
        'info' => 'trim|sanitize_string',
    ];

    public static function make($fields = [])
    {
        $validData = self::validate($fields, self::$rules, self::$filters);

        if ($validData['start_date'] > $validData['end_date'])
            throw new InvalidArgumentException('Start date must be before end date', 400);

        return new self($validData);
    }

    /* --------------------------------------------------------------------- */

    public static function orderBy(string $key = null, string $order = 'asc', array $conditions = [])
    {
        return parent::orderBy($key, $order, [
            sprintf('end_date >= "%s"', date('Y-m-d'))
        ]);
    }
}
