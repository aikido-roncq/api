<?php

namespace Utils;

/**
 * Arrays utils
 */
abstract class Arrays
{
  /**
   * Filter the given array to only keep the allowed keys
   * 
   * @param array $input the input array
   * @param array $allowedKeys the list of allowed keys
   * @return array the filtered array
   */
  public static function filterKeys(array $input, array $allowedKeys): array
  {
    return array_filter($input, function ($key) use ($allowedKeys) {
      return in_array($key, $allowedKeys);
    }, ARRAY_FILTER_USE_KEY);
  }

  /**
   * Check if all the keys of the keys array exist in the input one
   * 
   * @param array $input the input array
   * @param array $keys the keys to search for
   * @return bool true if all the keys exist
   */
  public static function allKeysExist(array $input, array $keys): bool
  {
    foreach ($keys as $key)
      if (!array_key_exists($key, $input))
        return false;

    return true;
  }
}
