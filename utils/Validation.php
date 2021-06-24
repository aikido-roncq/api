<?php

namespace Utils;

use Valitron\Validator;

/**
 * Validate user input
 */
abstract class Validation
{
  /**
   * Verify that the given input verifies the given rules
   * 
   * @param array $data the data to validate
   * @param array $rules the rules which the data should validate
   * @param array $labels the labels for each data
   * @return Validator the validator corresonding to the given parameters
   */
  public static function validate(array $data, array $rules, array $labels = []): Validator
  {
    $v = new Validator($data);

    $v->rules($rules);
    $v->labels($labels);

    return $v;
  }
}
