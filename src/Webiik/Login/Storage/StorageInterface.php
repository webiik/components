<?php
declare(strict_types=1);

namespace Webiik\Login\Storage;

interface StorageInterface
{
    /**
     * Store permanent login data
     * @param int|string $uid
     * @param string $selector
     * @param string $key
     * @param int $expiration Timestamp of permanent record expiration
     */
    public function store($uid, string $selector, string $key, int $expiration): void;

    /**
     * Get permanent login data by selector in associative array:
     * ['uid' => int|string, 'selector' => string, 'key' => string, 'expiration' => int]
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
     * Update expiration of permanent login data
     * @param string $selector
     * @param int $ttl Time to live in seconds
     */
    public function updateExpiration(string $selector, int $ttl): void;

    /**
     * Delete all expired permanent login data
     * @param int $ttl Time to live in seconds
     */
    public function deleteExpired(int $ttl): void;
}
