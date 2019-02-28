<?php
declare(strict_types=1);

namespace Webiik\Arr;

class Arr
{
    /**
     * Set the item into the array using the dot notation
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
     * Add the item into the array using the dot notation
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
     * Get the item from the array using the dot notation
     * @param array $array
     * @param string $key
     * @return mixed
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
     * Check if the item is in the array using the dot notation
     * @param array $array
     * @param string $key
     * @return bool
     */
    public function isIn(string $key, array $array): bool
    {
        $isIn = false;
        $keys = explode('.', $key);

        foreach ($keys as $ikey) {
            if (!isset($array[$ikey])) {
                $isIn = false;
                break;
            }
            $array = $array[$ikey];
            $isIn = true;
        }

        return $isIn;
    }

    /**
     * Delete the item from the array using the dot notation
     * @param string $key
     * @param array $array
     */
    public function delete(string $key, array &$array): void
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
