<?php
declare(strict_types=1);

namespace Webiik\Attempts;

interface StorageInterface
{
    /**
     * Write user attempt
     * @param string $label
     * @param string $ip
     * @param string $hash
     * @param int $timestamp
     */
    public function write(string $label, string $ip, string $hash = '', int $timestamp): void;

    /**
     * Read user attempts by label, ip and hash starting from startTimestamp
     * @param string $label
     * @param string $ip
     * @param string $hash
     * @param int $startTimestamp
     * @return array
     */
    public function read(string $label, string $ip, string $hash, int $startTimestamp = 0): array;

    /**
     * Read user attempts by user label and ip starting from startTimestamp
     * @param string $label
     * @param string $ip
     * @param int $startTimestamp
     * @return array
     */
    public function readByIp(string $label, string $ip, int $startTimestamp = 0): array;

    /**
     * Read user attempts by label and hash starting from startTimestamp
     * @param string $label
     * @param string $hash
     * @param int $startTimestamp
     * @return array
     */
    public function readByHash(string $label, string $hash, int $startTimestamp = 0): array;

    /**
     * Delete attempts by label older than timestamp
     * @param string $label
     * @param int $timestamp
     */
    public function delete(string $label, int $timestamp): void;

    /**
     * Delete all attempts older than timestamp
     * @param int $timestamp
     */
    public function deleteAll(int $timestamp): void;
}