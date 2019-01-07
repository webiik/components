<?php
declare(strict_types=1);

namespace Webiik\Log;

class Message
{
    /**
     * @var int
     */
    private $time;

    /**
     * @var string
     */
    private $level = '';

    /**
     * @var string
     */
    private $message = '';

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var array
     */
    private $groups = [];

    /**
     * LogMessage constructor.
     * @param string $level
     */
    public function __construct(string $level)
    {
        $this->level = $level;
        $this->setTime();
    }

    /**
     * @return string
     */
    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return Message
     */
    public function setMessage(string $message): Message
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return Message
     */
    public function setData(array $data): Message
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @param array $groups
     * @return Message
     */
    public function setGroups(array $groups): Message
    {
        $this->groups = $groups;
        return $this;
    }

    /**
     * @param string $group
     * @return Message
     */
    public function setGroup(string $group): Message
    {
        $this->groups = [$group];
        return $this;
    }

    /**
     * @return int
     */
    public function getTime(): int
    {
        return $this->time;
    }

    private function setTime(): void
    {
        $this->time = time();
    }
}
