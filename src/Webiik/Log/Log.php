<?php
declare(strict_types=1);

namespace Webiik\Log;

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
     * Array of Logger we will use for logging
     * @var array
     */
    private $loggers = [];

    /**
     * Messages we will log
     * @var array
     */
    private $messages = [];

    /**
     * In silent mode failed loggers not throw exceptions
     * instead of it these exceptions are logged with other loggers.
     * and failed loggers are skipped.
     * @var bool
     */
    private $silent = false;

    /**
     * @param bool $silent
     */
    public function setSilent(bool $silent): void
    {
        $this->silent = $silent;
    }

    /**
     * System is unusable
     *
     * @param string $message
     * @param array $context
     * @return Message
     */
    public function emergency(string $message, array $context = []): Message
    {
        return $this->log(self::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return Message
     */
    public function alert(string $message, array $context = []): Message
    {
        return $this->log(self::ALERT, $message, $context);
    }

    /**
     * Critical conditions
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return Message
     */
    public function critical(string $message, array $context = []): Message
    {
        return $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return Message
     */
    public function error(string $message, array $context = []): Message
    {
        return $this->log(self::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return Message
     */
    public function warning(string $message, array $context = []): Message
    {
        return $this->log(self::WARNING, $message, $context);
    }

    /**
     * Normal but significant events
     *
     * @param string $message
     * @param array $context
     * @return Message
     */
    public function notice(string $message, array $context = []): Message
    {
        return $this->log(self::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs
     *
     * @param string $message
     * @param array $context
     * @return Message
     */
    public function info(string $message, array $context = []): Message
    {
        return $this->log(self::INFO, $message, $context);
    }

    /**
     * Detailed debug information
     *
     * @param string $message
     * @param array $context
     * @return Message
     */
    public function debug(string $message, array $context = []): Message
    {
        return $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Create and store Message
     *
     * @link https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md#5-psrlogloglevel
     *
     * @param string $level
     * @param string $message
     * @param array $context
     * @return Message
     */
    public function log(string $level, string $message, array $context = []): Message
    {
        if ($context) {
            $message = $this->parseContext($message, $context);
        }

        $logMessage = new Message($level);
        $logMessage->setMessage($message);

        $this->messages[] = $logMessage;

        return $logMessage;
    }

    /**
     * Create Logger with factory of underlying logger service
     * @param callable $factory
     * @return Logger
     */
    public function addLogger(callable $factory): Logger
    {
        $logger = new Logger($factory);
        $this->loggers[] = $logger;
        return $logger;
    }

    /**
     * Remove logger
     * @param int $index
     */
    private function delLogger(int $index): void
    {
        unset($this->loggers[$index]);
    }

    /**
     * Write log records using the registered loggers and then remove all records
     * @throws \Exception
     */
    public function write(): void
    {
        $catchedExceptions = [];

        foreach ($this->messages as $message) {
            /* @var $message Message */
            $messageLevel = $message->getLevel();
            $messageGroups = $message->getGroups();

            foreach ($this->loggers as $loggerIndex => $logger) {
                /** @var Logger $logger */

                // When logger has level(s) set, it has to log only messages belonging to same level(s)
                $levelMatch = $this->levelMatch($messageLevel, $logger->getLevels());

                // When logger belongs in group(s), it has to log only messages belonging to same group(s)
                $groupMatch = $this->groupMatch($messageGroups, $logger->getGroups());

                // When logger belongs in negative group(s), don't log message in same group
                $negativeGroupMatch = $this->negativeGroupMatch($messageGroups, $logger->getNegativeGroups());

                if ($levelMatch && $groupMatch && !$negativeGroupMatch) {
                    // Log this message with this logger
                    try {
                        $logger->getInstance()->write($message);
                    } catch (\Exception $exception) {
                        // When logger throws an exception, remove it from loggers
                        // and then log the exception. It is necessary for safe
                        // and uninterrupted logging.
                        $this->delLogger($loggerIndex);
                        $catchedExceptions[] = $exception;
                    } catch (\TypeError $exception) {
                        $this->delLogger($loggerIndex);
                        $catchedExceptions[] = $exception;
                    } catch (\Throwable $exception) {
                        $this->delLogger($loggerIndex);
                        $catchedExceptions[] = $exception;
                    }
                }
            }
        }

        // Clear all written Messages
        $this->clearLogs();

        if ($this->silent) {
            // Log exceptions from failed loggers
            $this->logExceptions($catchedExceptions);
        } else {
            // Throw exception from first failed logger
            $this->throwExceptions($catchedExceptions);
        }
    }

    /**
     * Determine if message level matches logger level
     * @param string $messageLevel
     * @param array $loggerLevels
     * @return bool
     */
    private function levelMatch(string $messageLevel, array $loggerLevels): bool
    {
        $levelMatch = false;

        if (!$loggerLevels) {
            // Logger has not specified level, it can log every message level
            $levelMatch = true;
        }

        if (in_array($messageLevel, $loggerLevels)) {
            // Logger belong to specified levels, test if message too
            $levelMatch = true;
        }

        return $levelMatch;
    }

    /**
     * Determine if message group matches logger group
     * @param array $messageGroups
     * @param array $loggerGroups
     * @return bool
     */
    private function groupMatch(array $messageGroups, array $loggerGroups): bool
    {
        $groupMatch = false;

        if (!$loggerGroups) {
            // Logger doesn't belong to any group, it can log every message group
            $groupMatch = true;
        }

        foreach ($loggerGroups as $loggerGroup) {
            // Logger belongs to specified groups, test if message too
            if (in_array($loggerGroup, $messageGroups)) {
                $groupMatch = true;
                break;
            }
        }

        return $groupMatch;
    }

    /**
     * Determine if message group matches negative logger group
     * @param array $messageGroups
     * @param array $loggerNegativeGroups
     * @return bool
     */
    private function negativeGroupMatch(array $messageGroups, array $loggerNegativeGroups): bool
    {
        $groupMatch = false;

        foreach ($loggerNegativeGroups as $loggerNegativeGroup) {
            // Logger belongs to specified groups, test if message too
            if (in_array($loggerNegativeGroup, $messageGroups)) {
                $groupMatch = true;
                break;
            }
        }

        return $groupMatch;
    }

    /**
     * Clear all stored Messages
     */
    private function clearLogs(): void
    {
        $this->messages = [];
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
                $msg = '[Exception] {message} in {file} on line {line}.';
                $context = [
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ];
                $this->warning($msg, $context)
                    ->setData($exception->getTrace())
                    ->setGroup('error');
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
            $val = is_numeric($val) ? $val : htmlspecialchars($val);
            $replace['{' . $key . '}'] = $val;
        }
        return strtr($message, $replace);
    }
}
