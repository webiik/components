<?php
declare(strict_types=1);

namespace Webiik\Data;

class Data
{
    /**
     * Key-value array
     * @var array
     */
    private $data = [];

    /**
     * @param string $key
     * @param mixed $data
     */
    public function set(string $key, $data): void
    {
        $this->data[$key] = $data;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function isIn(string $key)
    {
        return isset($this->data[$key]);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->data[$key];
    }

    /**
     * @return mixed
     */
    public function getAll()
    {
        return $this->data;
    }
}
