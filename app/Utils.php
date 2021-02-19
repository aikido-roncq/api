<?php

namespace App;

use App\Exceptions\ValidationException;
use Valitron\Validator;

abstract class Utils
{
    public static function validate(array $data, array $rules, array $labels = [])
    {
        $v = new Validator($data);

        $v->rules($rules);
        $v->labels($labels);

        if (!$v->validate())
            throw new ValidationException($v->errors());

        return self::validData($data, $rules);
    }

    private static function validData(array $array, array $rules)
    {
        $rulesKeys = self::flattenArray($rules);

        return array_filter($array, function ($key) use ($rulesKeys) {
            return in_array($key, $rulesKeys);
        }, ARRAY_FILTER_USE_KEY);
    }

    private static function flattenArray(array $array)
    {
        $result = [];
        $queue = $array;

        while ($queue) {
            $value = array_shift($queue);

            if (is_array($value))
                array_unshift($queue, ...array_values($value));
            else
                $result[] = $value;
        }

        return $result;
    }
}
