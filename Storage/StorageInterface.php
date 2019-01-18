<?php
declare(strict_types=1);

namespace Webiik\Login\Storage;

interface StorageInterface
{
    /**
     * Store permanent login data
     * @param int|string $uid
     * @param string $role
     * @param string $selector
     * @param string $key
     * @param int $expiration Timestamp of permanent record expiration
     */
    public function store($uid, string $role, string $selector, string $key, int $expiration): void;

    /**
     * Get permanent login data by selector in associative array:
     * ['uid' => int|string, 'role' => string, 'selector' => string, 'key' => string, 'expiration' => int]
     * @param string $selector
     * @return array
     */
    public function get(string $selector): array;

    /**
     * Delete permanent login data by selector
     * @param string $selector
     */
    public function delete(string $selector): void;

    /**
     * Delete all expired permanent login data
     *
     * Warning:
     * TTL(time to live) with value 0 means that data will never expire. Consider to delete
     * data that have been not accessed for a longer period of time to prevent filling-up
     * whole free space of storage with unnecessary garbage.
     *
     * @param int $ttl Time to live in seconds
     */
    public function deleteExpired(int $ttl): void;
}
