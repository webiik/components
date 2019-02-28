<?php
declare(strict_types=1);

namespace Webiik\Log;

use Webiik\Log\Logger\LoggerInterface;

class Logger
{
    /**
     * @var array
     */
    private $levels = [];

    /**
     * @var array
     */
    private $groups = [];

    /**
     * @var array
     */
    private $negativeGroups = [];

    /**
     * Logger factory or instance after call getInstance
     * @var callable|LoggerInterface
     */
    private $logger;

    /**
     * Logger constructor.
     * @param callable $factory
     */
    public function __construct(callable $factory)
    {
        $this->logger = $factory;
    }

    /**
     * @return array
     */
    public function getLevels(): array
    {
        return $this->levels;
    }

    /**
     * @param array $levels
     * @return Logger
     */
    public function setLevels(array $levels): Logger
    {
        $this->levels = $levels;
        return $this;
    }

    /**
     * @param string $level
     * @return Logger
     */
    public function setLevel(string $level): Logger
    {
        $this->levels = [$level];
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
     * @return Logger
     */
    public function setGroups(array $groups): Logger
    {
        $this->groups = $groups;
        return $this;
    }

    /**
     * @param string $group
     * @return Logger
     */
    public function setGroup(string $group): Logger
    {
        $this->groups = [$group];
        return $this;
    }

    /**
     * @return array
     */
    public function getNegativeGroups(): array
    {
        return $this->negativeGroups;
    }

    /**
     * @param array $groups
     * @return Logger
     */
    public function setNegativeGroups(array $groups): Logger
    {
        $this->negativeGroups = $groups;
        return $this;
    }

    /**
     * @param string $group
     * @return Logger
     */
    public function setNegativeGroup(string $group): Logger
    {
        $this->negativeGroups = [$group];
        return $this;
    }

    /**
     * Get underlying logger instance
     * @return LoggerInterface
     */
    public function getInstance(): LoggerInterface
    {
        // Instantiate logger only once
        if (is_callable($this->logger)) {
            $logger = $this->logger;
            $this->logger = $logger();
        }
        return $this->logger;
    }
}
