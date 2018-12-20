<?php
declare(strict_types=1);

namespace Webiik\Log;

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
     * Log loggers we will use for logging
     * @var array
     */
    private $loggers = [];

    /**
     * Records we will log
     * @var array
     */
    private $records = [];

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
            $message = $this->parse($message, $context);
        }

        $this->records[] = new Record($level, $message, $data);
    }

    /**
     * Add logger to appropriate log level(s)
     * @param LoggerInterface $logger
     * @param array $levels
     */
    public function addLogger(LoggerInterface $logger, array $levels = []): void
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

            /* @var $logger LoggerInterface */
            $logger = $arr[0];
            $logger->process($loggerRecords);
        }

        $this->records = [];
    }

    /**
     * @param string $message
     * @param array $context
     * @return string
     */
    private function parse(string $message, array $context): string
    {
        $replace = [];
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }
        $message = strtr($message, $replace);
        return $message;
    }
}
