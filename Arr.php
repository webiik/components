<?php
declare(strict_types=1);

namespace Webiik\Arr;

class Arr
{
    /**
     * Set an item into array using dot notation
     * @param string $key
     * @param mixed $val
     * @param array $array
     */
    public function set(string $key, $val, array &$array): void
    {
        $keys = explode('.', $key);
        $context = &$array;
        foreach ($keys as $ikey) {
            $context = &$context[$ikey];
        }

        $context = $val;
    }

    /**
     * Add an item into array using dot notation
     * @param string $key
     * @param mixed $val
     * @param array $array
     */
    public function add(string $key, $val, array &$array): void
    {
        $keys = explode('.', $key);
        $context = &$array;
        foreach ($keys as $ikey) {
            $context = &$context[$ikey];
        }

        if (empty($context)) {
            $context = $val;
            return;
        }

        if (!is_array($context)) {
            $context = [$context];
        }

        if (!is_array($val)) {
            $val = [$val];
        }

        $context = array_merge($context, $val);
    }

    /**
     * Get an item from array using dot notation
     * @param array $array
     * @param string $key
     * @return mixed|bool
     */
    public function get(string $key, array $array)
    {
        $keys = explode('.', $key);

        foreach ($keys as $ikey) {
            $array = $array[$ikey];
        }

        return $array;
    }

    /**
     * Delete an item from array using dot notation
     * On success return key value, otherwise false
     * @param string $key
     * @param array $array
     */
    public function delete(string $key, array &$array)
    {
        $keys = explode('.', $key);
        $context = &$array;
        $last = array_pop($keys);

        foreach ($keys as $ikey) {
            $context = &$context[$ikey];
        }

        unset($context[$last]);
    }
}
