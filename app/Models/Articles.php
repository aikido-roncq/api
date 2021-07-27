<?php

namespace App\Models;

use App\Exceptions\ValidationException;

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
    'id', 'date', 'title', 'content'
  ];

  /**
   * Validation rules
   */
  protected static array $rules = [
    'required' => [
      'date', 'title', 'content'
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
    $fields['date'] = date(self::DATE_FORMAT);
    return new self($fields);
  }
}
