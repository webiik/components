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
     * In silent mode unavailable loggers not throw exceptions
     * instead of it these exceptions are logged with available loggers.
     * @var bool
     */
    private $silent = false;

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
     * @param bool $silent
     */
    public function setSilent(bool $silent): void
    {
        $this->silent = $silent;
    }


    /**
     * Todo: Implement groups
     * @param string $message
     * @param array $context
     * @param array $data
     * @param string $group
     */
    public function info(string $message, array $context = [], array $data = [], string $group = ''): void
    {
    }

    // Todo: Add another PSR-3 log level methods

    /**
     * Add logger with appropriate log level(s)
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

        $this->loggers[$logger] = $levels;
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
    private function log(string $level, string $message, array $context = [], array $data = []): void
    {
        if ($context) {
            $message = $this->parseContext($message, $context);
        }

        $this->records[] = new Record($level, $message, $data);
    }

    /**
     * Remove logger
     * @param string $logger
     */
    private function delLogger(string $logger): void
    {
        unset($this->loggers[$logger]);
    }


    /**
     * Write log records using the registered loggers and remove all records
     * @throws \Exception
     */
    public function write(): void
    {
        $catchedExceptions = [];

        foreach ($this->loggers as $loggerName => $loggerLogLevels) {
            // Prepare only records associated with log level(s) of iterated logger
            $loggerRecords = [];
            foreach ($this->records as $record) {
                /* @var $record Record */
                $recordLogLevel = $record->getLevel();
                foreach ($loggerLogLevels as $loggerLogLevel) {
                    if ($loggerLogLevel == $recordLogLevel) {
                        $loggerRecords[] = $record;
                    }
                }
            }

            // Process records
            if ($loggerRecords) {
                try {
                    /** @var LoggerInterface $logger */
                    $logger = $this->container->get($loggerName);
                    $logger->process($loggerRecords);
                } catch (\Exception $exception) {
                    // When logger doesn't exist remove it from loggers and log exception.
                    // This is necessary for safe and uninterrupted logging.
                    $this->delLogger($loggerName);
                    $catchedExceptions[] = $exception;
                } catch (\TypeError $exception) {
                    $this->delLogger($loggerName);
                    $catchedExceptions[] = $exception;
                }
            }
        }

        // Clear all written records
        $this->records = [];

        if ($this->silent) {
            // Log exceptions about non existing loggers
            $this->logExceptions($catchedExceptions);
        } else {
            // Throw exception from first non existing logger
            $this->throwExceptions($catchedExceptions);
        }
    }

    /**
     * Log exceptions in Exceptions array
     * @param array $exceptions
     * @throws \Exception
     */
    private function logExceptions(array $exceptions): void
    {
        if ($exceptions) {
            foreach ($exceptions as $exception) {
                /** @var \Exception $exception */
                $msg = '[Exception] ' . $exception->getMessage() . ' in ' . $exception->getFile();
                $msg .= ' on line ' . $exception->getLine();
                $this->log(self::WARNING, $msg, [], $exception->getTrace());
            }
            $this->write();
        }
    }

    /**
     * Throw first exceptions in Exceptions array
     * @param array $exceptions
     * @throws \Exception
     */
    private function throwExceptions(array $exceptions): void
    {
        foreach ($exceptions as $exception) {
            /** @var \Exception $exception */
            throw $exception;
        }
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
