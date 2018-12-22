<?php
declare(strict_types=1);

namespace Webiik\Log;

use Webiik\Container\Container;
use Webiik\Log\Logger\LoggerInterface;

class Log
{
    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';

    /**
     * @var Container
     */
    private $container;

    /**
     * Loggers we will use for logging
     * @var array
     */
    private $loggers = [];

    /**
     * Records we will log
     * @var array
     */
    private $records = [];

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Logs with an arbitrary level in PSR-3 format
     * @link https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md#5-psrlogloglevel
     *
     * @param string $level PSR-3 log level
     * @param string $message
     * @param array $context
     * @param array $data
     */
    public function log(string $level, string $message, array $context = [], array $data = []): void
    {
        if ($context) {
            $message = $this->parseContext($message, $context);
        }

        $this->records[] = new Record($level, $message, $data);
    }

    /**
     * Add logger to appropriate log level(s)
     * @param string $logger
     * @param array $levels
     */
    public function addLogger(string $logger, array $levels = []): void
    {
        if (!$levels) {
            $levels = [
                self::EMERGENCY,
                self::ALERT,
                self::CRITICAL,
                self::ERROR,
                self::WARNING,
                self::NOTICE,
                self::INFO,
                self::DEBUG,
            ];
        }

        $this->loggers[] = [$logger, $levels];
    }

    /**
     * Write log records using the registered loggers and remove all records
     */
    public function write(): void
    {
        foreach ($this->loggers as $arr) {
            $loggerRecords = [];
            foreach ($this->records as $record) {
                /* @var $record Record */
                $recordLogLevel = $record->getLevel();
                foreach ($arr[1] as $loggerLoglevel) {
                    if ($loggerLoglevel == $recordLogLevel) {
                        $loggerRecords[] = $record;
                    }
                }
            }

            /** @var LoggerInterface $logger */
            $logger = $this->container->get($arr[0]);
            $logger->process($loggerRecords);
        }

        $this->records = [];
    }

    /**
     * @param string $message
     * @param array $context
     * @return string
     */
    private function parseContext(string $message, array $context): string
    {
        $replace = [];
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }
        return strtr($message, $replace);
    }
}
