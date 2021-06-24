<?php

namespace App\Models;

use App\Exceptions\NotFoundException;
use App\Exceptions\UnknownException;
use App\Exceptions\ValidationException;
use Cocur\Slugify\Slugify;
use PDOException;

/**
 * Articles model
 */
class Articles extends Model
{
  /**
   * Format of the date
   */
  private const DATE_FORMAT = 'Y-m-d H:i:s';

  /**
   * Primary key
   */
  protected static string $pk = 'id';

  /**
   * Table keys
   */
  protected static array $keys = [
    'id', 'date', 'slug', 'title', 'content'
  ];

  /**
   * Validation rules
   */
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

  /**
   * Fields labels
   */
  protected static array $labels = [
    'title' => 'Le titre',
    'content' => 'Le contenu'
  ];

  /**
   * Create an article from a list of fields
   * 
   * @param array $fields the article fields (title and content)
   * @return self the newly created article
   * @throws ValidationException on data error 
   */
  protected static function make(array $fields = []): self
  {
    if (array_key_exists('title', $fields))
      $fields['slug'] = self::slugify($fields['title']);

    $fields['date'] = date(self::DATE_FORMAT);

    return new self($fields);
  }

  /**
   * Update an article from its primary key
   * 
   * @param string $key the key of the article
   * @param array $fields the fields to update
   * @return self the newly created article
   * @throws ValidationException on data error
   * @throws NotFoundException if the article doesn't exist
   * @throws PDOException on PDO error
   * @throws UnknownException on unknown error
   */
  public static function update(string $key, array $fields): self
  {
    if (array_key_exists('title', $fields))
      $fields['slug'] = self::slugify($fields['title']);

    return parent::update($key, $fields);
  }

  /**
   * Convert the title to a slug
   * 
   * @param string $title the title to slugify
   * @return string the slugified title
   */
  private static function slugify(string $title): string
  {
    $slug = (new Slugify)->slugify($title);
    $token = hash('crc32', microtime());
    return sprintf('%s-%s', $slug, $token);
  }
}
