<?php

namespace Utils;

use Valitron\Validator;

abstract class Validation
{
  public static function validate(array $data, array $rules, array $labels = []): Validator
  {
    $v = new Validator($data);

    $v->rules($rules);
    $v->labels($labels);

    return $v;
  }
}
