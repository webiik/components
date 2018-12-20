<?php
declare(strict_types=1);

namespace Webiik\Log;

class Record
{
    /**
     * @var array
     */
    private $record;

    /**
     * Record constructor.
     * @param string $level
     * @param string $message
     * @param array $data
     */
    public function __construct(string $level, string $message, array $data)
    {
        $this->record = [time(), $level, $message, $data];
    }

    /**
     * @return int
     */
    public function getTime(): int
    {
        return $this->record[0];
    }

    /**
     * @return string
     */
    public function getLevel(): string
    {
        return $this->record[1];
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->record[2];
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->record[3];
    }
}
