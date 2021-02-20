<?php

namespace App\Models;

use Cocur\Slugify\Slugify;

class Articles extends Model
{
  private const DATE_FORMAT = 'Y-m-d H:i:s';

  protected static string $pk = 'slug';

  protected static array $keys = [
    'date', 'slug', 'title', 'content'
  ];

  protected static array $rules = [
    'required' => [
      'date', 'slug', 'title', 'content'
    ],
    'lengthBetween' => [
      ['title', 5, 50]
    ],
    'lengthMin' => [
      ['content', 10]
    ],
    'dateFormat' => [
      ['date', self::DATE_FORMAT]
    ]
  ];

  protected static array $labels = [
    'title' => 'Le titre',
    'content' => 'Le contenu'
  ];

  protected static function make(array $fields = [])
  {
    if (array_key_exists('title', $fields))
      $fields['slug'] = self::slugify($fields['title']);

    $fields['date'] = date(self::DATE_FORMAT);

    return new self($fields);
  }

  public static function update(string $key, array $fields)
  {
    if (array_key_exists('title', $fields))
      $fields['slug'] = self::slugify($fields['title']);

    return parent::update($key, $fields);
  }

  private static function slugify(string $title)
  {
    $slug = (new Slugify)->slugify($title);
    $token = bin2hex(random_bytes(2));
    return  sprintf('%s-%s', $slug, $token);
  }
}
