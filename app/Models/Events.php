<?php

namespace App\Models;

use App\Exceptions\ValidationException;
use App\Utils;

class Events extends Model
{
  protected static string $pk = 'id';

  protected static array $keys = [
    'id', 'title', 'info', 'start_date', 'end_date'
  ];

  protected static array $rules = [
    'required' => ['title', 'info', 'start_date', 'end_date'],
    'dateFormat' => [
      ['start_date', 'Y-m-d'],
      ['end_date', 'Y-m-d'],
    ],
    'lengthBetween' => [
      ['title', 3, 50],
      ['info', 5, 250],
    ],
  ];

  protected static array $labels = [
    'title' => 'Le titre',
    'info' => 'Le champ informations',
    'start_date' => 'La date de début',
    'end_date' => 'La date de fin',
  ];

  public static function make(array $data = []): self
  {
    if (Utils::allKeysExist(['start_date', 'end_date'], $data))
      if ($data['start_date'] > $data['end_date'])
        throw new ValidationException([
          'end_date' => 'La date de fin ne peut pas être avant celle de début'
        ]);

    return new self($data);
  }
}
