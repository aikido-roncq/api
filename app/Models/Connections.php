<?php

namespace App\Models;

use App\Exceptions\NotFoundException;
use App\Config;

class Connections extends Model
{
  protected static string $pk = 'token';

  protected static array $rules = [
    'required' => ['token', 'iat', 'exp'],
    'dateFormat' => [
      ['iat', 'Y-m-d H:i:s'],
      ['exp', 'Y-m-d H:i:s'],
    ]
  ];

  protected static array $labels = [];

  public static function make(array $fields = [])
  {
    $fields = [
      'token' => bin2hex(random_bytes(16)),
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

  public static function isValid(string $token)
  {
    try {
      $record = self::find($token);
    } catch (NotFoundException $e) {
      return false;
    }

    return $record->exp < date('Y-m-d H:i:s');
  }
}
