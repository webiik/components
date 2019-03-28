<?php
declare(strict_types=1);

namespace Webiik\Attempts;

class Attempts
{
    /**
     * @var callable|StorageInterface
     */
    private $storage;

    /**
     * @param callable $factory
     */
    public function setStorage(callable $factory): void
    {
        $this->storage = $factory;
    }

    /**
     * Write user attempt to the storage
     * @param string $label
     * @param string $ip
     * @param string $hash
     */
    public function write(string $label, string $ip, string $hash = ''): void
    {
        $this->getStorage()->write($label, $ip, $hash, time());
    }

    /**
     * Read user attempts from the storage
     * @param string $label
     * @param string $ip
     * @param string $hash
     * @param int $startTimestamp
     * @return array
     */
    public function read(string $label, string $ip, string $hash, int $startTimestamp = 0): array
    {
        return $this->getStorage()->read($label, $ip, $hash, $startTimestamp);
    }

    /**
     * Read user attempts from the storage by user ip
     * @param string $label
     * @param string $ip
     * @param int $startTimestamp
     * @return array
     */
    public function readByIp(string $label, string $ip, int $startTimestamp = 0): array
    {
        return $this->getStorage()->readByIp($label, $ip, $startTimestamp);
    }

    /**
     * Read user attempts from the storage by user hash
     * @param string $label
     * @param string $hash
     * @param int $startTimestamp
     * @return array
     */
    public function readByHash(string $label, string $hash, int $startTimestamp = 0): array
    {
        return $this->getStorage()->readByHash($label, $hash, $startTimestamp);
    }

    /**
     * Get user IP
     * @return string
     */
    public function getIp(): string
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Delete expired attempts from storage
     * @param string $label
     * @param int $timestamp
     * @param int $probability
     */
    public function delete(string $label, int $timestamp, int $probability = 1): void
    {
        if (rand(1, 100) <= $probability) {
            $this->getStorage()->delete($label, $timestamp);
        }
    }

    /**
     * Delete expired attempts from storage
     * @param int $timestamp
     * @param int $probability
     */
    public function deleteAll(int $timestamp, int $probability = 1): void
    {
        if (rand(1, 100) <= $probability) {
            $this->getStorage()->deleteAll($timestamp);
        }
    }

    /**
     * Get storage for permanent login identifiers
     * @return StorageInterface
     */
    private function getStorage(): StorageInterface
    {
        // Instantiate storage only once
        if (is_callable($this->storage)) {
            $storage = $this->storage;
            $this->storage = $storage();
        }
        return $this->storage;
    }
}
