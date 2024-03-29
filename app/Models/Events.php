<?php

namespace App\Models;

use App\Exceptions\ValidationException;
use Utils\Arrays;

/**
 * Events model
 */
class Events extends Model
{
  /**
   * The primary key
   */
  protected static string $pk = 'id';

  /**
   * Table keys
   */
  protected static array $keys = [
    'id', 'title', 'info', 'start_date', 'end_date'
  ];

  /**
   * Validation rules
   */
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

  /**
   * Fields labels
   */
  protected static array $labels = [
    'title' => 'Le titre',
    'info' => 'Le champ informations',
    'start_date' => 'La date de début',
    'end_date' => 'La date de fin',
  ];

  /**
   * Create a new event from input data
   * 
   * @param array $data the input data (start_date, end_date, etc)
   * @return self the newly created event
   */
  public static function make(array $data = []): self
  {
    if (Arrays::allKeysExist($data, ['start_date', 'end_date'])) {
      if ($data['start_date'] > $data['end_date']) {
        throw new ValidationException('event start date is after end date', [
          'end_date' => 'La date de fin ne peut pas être avant celle de début'
        ]);
      }
    }

    return new self($data);
  }
}
