<?php

namespace App\Models;

class Gallery extends Model
{
  protected static string $pk = 'id';

  protected static array $keys = [
    'id', 'src', 'caption', 'added'
  ];

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

  protected static array $labels = [
    'caption' => 'La légende'
  ];

  public static function make(array $data = []): self
  {
    return new self($data);
  }
}
