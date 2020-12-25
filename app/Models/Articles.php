<?php

namespace App\Models;

use Cocur\Slugify\Slugify;

class Articles extends Model
{
    protected static $pk = 'slug';

    protected static $rules = [
        'slug' => 'required',
        'title' => 'required|between_len,3;50',
        'content' => 'required|min_len,3'
    ];

    protected static $filters = [
        'title' => 'trim|sanitize_string',
        'content' => 'trim'
    ];

    protected static function make(array $fields = [])
    {
        $fields['slug'] = self::slugify($fields['title'] ?? '');
        $validData = static::validate($fields, self::$rules, self::$filters);
        return new self($validData);
    }

    /* --------------------------------------------------------------------- */

    public static function update($key, $new_fields)
    {
        if (array_key_exists('title', $new_fields))
            $new_fields['slug'] = self::slugify($new_fields['title']);

        return parent::update($key, $new_fields);
    }

    /* --------------------------------------------------------------------- */

    private static function slugify(string $title)
    {
        return (new Slugify)->slugify($title) . '-' . bin2hex(random_bytes(2));
    }
}
