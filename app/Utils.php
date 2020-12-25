<?php

namespace App;

use GUMP;
use InvalidArgumentException;

abstract class Utils
{
    public static function validate(array $data, array $rules, array $filters = [])
    {
        $g = new GUMP($_ENV['APP_LANG']);

        $g->validation_rules($rules);
        $g->filter_rules($filters);
        $g->run($data);

        if ($g->errors()) {
            $errors = array_map(function ($error) {
                return "$error.";
            }, $g->get_errors_array());
            $errorsStr = implode(' ', $errors);
            throw new InvalidArgumentException($errorsStr, 400);
        }

        $allowed = array_keys($rules);

        $validData = array_filter($data, function ($key) use ($allowed) {
            return in_array($key, $allowed);
        }, ARRAY_FILTER_USE_KEY);

        return $validData;
    }
}
