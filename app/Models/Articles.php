<?php

namespace App\Models;

use App\Exceptions\ValidationException;
use App\Utils;
use Cocur\Slugify\Slugify;
use InvalidArgumentException;

class Articles extends Model
{
    protected static string $pk = 'slug';

    protected static array $rules = [
        'required' => ['slug', 'title', 'content'],
        'lengthBetween' => ['title', 3, 50],
        'lengthMin' => ['content', 3],
    ];

    protected static array $labels = [
        'title' => 'Le titre',
        'content' => 'Le contenu'
    ];

    protected static function make(array $fields = [])
    {
        if (array_key_exists('title', $fields))
            $fields['slug'] = self::slugify($fields['title']);

        $validData = Utils::filterKeys($fields, ['slug', 'title', 'content']);

        return new self($validData);
    }

    public static function update(string $key, array $new_fields)
    {
        if (array_key_exists('title', $new_fields))
            $new_fields['slug'] = self::slugify($new_fields['title']);

        return parent::update($key, $new_fields);
    }

    private static function slugify(string $title)
    {
        $slug = (new Slugify)->slugify($title);
        $token = bin2hex(random_bytes(2));
        return  "$slug-$token";
    }
}
