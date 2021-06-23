<?php

namespace App\Models;

use App\Exceptions\NotFoundException;
use App\Config;
use Utils\Logger;

class Connections extends Model
{
  protected static string $pk = 'token';

  protected static array $keys = [
    'token', 'iat', 'exp'
  ];

  protected static array $rules = [
    'required' => ['token', 'iat', 'exp'],
    'dateFormat' => [
      ['iat', 'Y-m-d H:i:s'],
      ['exp', 'Y-m-d H:i:s'],
    ]
  ];

  protected static array $labels = [];

  public static function make(array $fields = []): self
  {
    $fields = [
      'token' => hash('sha256', microtime()),
      'iat' => date('Y-m-d H:i:s'),
      'exp' => date('Y-m-d H:i:s', time() + Config::TOKEN_LIFETIME),
    ];

    return new static($fields);
  }

  public static function revoke(string $token)
  {
    self::update($token, [
      'exp' => date('Y-m-d H:i:s')
    ]);
  }

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
