<?php

namespace App\Models;

use App\Exceptions\NotFoundException;
use App\Config;
use App\Exceptions\ValidationException;
use PDOException;
use Utils\Logger;

/**
 * Connections model
 */
class Connections extends Model
{
  /**
   * Primary key
   */
  protected static string $pk = 'token';

  /**
   * Table keys
   */
  protected static array $keys = [
    'token', 'iat', 'exp'
  ];

  /**
   * Validation rules
   */
  protected static array $rules = [
    'required' => ['token', 'iat', 'exp'],
    'dateFormat' => [
      ['iat', 'Y-m-d H:i:s'],
      ['exp', 'Y-m-d H:i:s'],
    ]
  ];

  /**
   * Fields labels
   */
  protected static array $labels = [];

  /**
   * Create a new connection
   * 
   * @param array $fields the fields
   * @return self the newly created connection
   * @throws ValidationException on fields error
   */
  public static function make(array $fields = []): self
  {
    $fields = [
      'token' => hash('sha256', microtime()),
      'iat' => date('Y-m-d H:i:s'),
      'exp' => date('Y-m-d H:i:s', time() + Config::TOKEN_LIFETIME),
    ];

    return new static($fields);
  }

  /**
   * @throws NotFoundException
   * @throws ValidationException
   * @throws PDOException
   * @throws UnknownException
   */
  public static function revoke(string $token)
  {
    self::update($token, [
      'exp' => date('Y-m-d H:i:s')
    ]);
  }

  /**
   * @throws PDOException
   */
  public static function isValid(string $token): bool
  {
    try {
      $record = self::find($token);
    } catch (NotFoundException $e) {
      Logger::error('invalid token');
      return false;
    }

    return date('Y-m-d H:i:s') < $record->exp;
  }
}
