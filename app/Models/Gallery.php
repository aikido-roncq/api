<?php

namespace App\Models;

/**
 * Gallery model
 */
class Gallery extends Model
{
  /**
   * The primary key
   */
  protected static string $pk = 'id';

  /**
   * Table keys
   */
  protected static array $keys = [
    'id', 'src', 'caption', 'added'
  ];

  /**
   * Validation rules
   */
  protected static array $rules = [
    'required' => ['src'],
    'optional' => ['caption'],
    'lengthBetween' => [
      ['caption', 5, 250]
    ],
    'dateFormat' => [
      ['added', 'Y-m-d H:i:s']
    ],
    'integer' => ['id']
  ];

  /**
   * Fields labels
   */
  protected static array $labels = [
    'caption' => 'La légende'
  ];

  /**
   * Create a new image from input data
   * 
   * @param array $data input data
   * @return self the newly created image
   */
  public static function make(array $data = []): self
  {
    return new self($data);
  }
}
