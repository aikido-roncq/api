<?php

namespace App;

use Valitron\Validator;

abstract class Utils
{
  public static function validate(array $data, array $rules, array $labels = [])
  {
    $v = new Validator($data);

    $v->rules($rules);
    $v->labels($labels);

    return $v;
  }

  public static function filterKeys(array $input, array $allowedKeys)
  {
    return array_filter($input, function ($key) use ($allowedKeys) {
      return in_array($key, $allowedKeys);
    }, ARRAY_FILTER_USE_KEY);
  }
}
