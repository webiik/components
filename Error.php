<?php
declare(strict_types=1);

namespace Webiik\Error;

use Webiik\Log\Log;

class Error
{
    /**
     * @var Log
     */
    private $log;

    /**
     * @var string
     */
    private $errToLogDefLevel = 'warning';

    /**
     * @var array
     */
    private $errToLogLevel = [];

    /**
     * @var bool
     */
    private $silent = false;

    /**
     * @var array
     */
    private $silentIngoreErrors = [
        'E_WARNING',
        'E_CORE_WARNING',
        'E_COMPILE_WARNING',
        'E_USER_WARNING',
        'E_NOTICE',
        'E_USER_NOTICE',
        'E_DEPRECATED',
        'E_USER_DEPRECATED',
    ];

    /**
     * @var string
     */
    private $silentPageContent;

    public function __construct()
    {
        // Configure error reporting
        ini_set('log_errors', '0');
        ini_set('display_errors', '0');
        ini_set('error_reporting', (string)E_ALL);

        // Set custom error handlers
        set_error_handler($this->errorHandler());
        register_shutdown_function($this->shutdownHandler());
        set_exception_handler($this->exceptionHandler());
    }

    /**
     * Override default use of error_log by Log
     * @param Log $log
     */
    public function setLog(Log $log): void
    {
        $this->log = $log;
    }

    /**
     * Set default log level of php errors when using Log
     * Will be used for all php errors without associated log level
     * @param $level
     */
    public function setErrLogDefLevel($level): void
    {
        $this->errToLogDefLevel = $level;
    }

    /**
     * Set log level of specific php error when using Log
     * @param $error
     * @param $level
     */
    public function addErrLogLevel($error, $level): void
    {
        $this->errToLogLevel[$error] = [$level];
    }

    /**
     * Determine if silent mode is used
     * In silent mode, errors are logged but not displayed
     * @param bool $bool
     */
    public function silent(bool $bool): void
    {
        $this->silent = $bool;
    }

    /**
     * Register user defined silent page content
     * @param string $string
     */
    public function setSilentPageContent(string $string): void
    {
        $this->silentPageContent = $string;
    }

    /**
     * Set which error types will not halt the app in silent mode
     * @link http://php.net/manual/en/errorfunc.constants.php
     * @param array $arr
     */
    public function setSilentIgnoreErrors(array $arr): void
    {
        $this->silentIngoreErrors = $arr;
    }

    /**
     * Return error type by error number
     * @return array
     */
    private function getErrTypes(): array
    {
        $errTypes = [];
        $phpDefinedConstants = get_defined_constants(true)['Core'];
        foreach ($phpDefinedConstants as $key => $val) {
            if (preg_match('/^E_/', $key)) {
                $errTypes[$val] = (string)$key;
            }
        }
        return $errTypes;
    }

    /**
     * Return error type by error number
     * @param int $errno
     * @return string
     */
    private function getErrType(int $errno): string
    {
        $errTypes = $this->getErrTypes();
        return isset($errTypes[$errno]) ? $errTypes[$errno] : (string)$errno;
    }

    /**
     * Return exception handler function
     * @return callable
     */
    private function exceptionHandler(): callable
    {
        /** @param \Error|\Exception $exception */
        $exceptionHandler = function ($exception) {
            $this->outputError(
                'Exception',
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                $this->getFormattedTrace($exception->getTrace())
            );
        };
        return $exceptionHandler;
    }

    /**
     * Return error handler function
     * @return callable
     */
    private function errorHandler(): callable
    {
        $errorHandler = function ($errno, $errstr, $errfile, $errline) {
            if ($errno && error_reporting()) {
                $this->outputError(
                    $this->getErrType($errno),
                    $errstr,
                    $errfile,
                    $errline,
                    $this->getFormattedTrace(debug_backtrace())
                );
            }
        };
        return $errorHandler;
    }

    /**
     * Return shutdown handler function
     * @return callable
     */
    private function shutdownHandler(): callable
    {
        $shutdownHandler = function () {
            $err = error_get_last();
            if ($err && $err['type'] === E_ERROR) {
                $this->outputError(
                    $this->getErrType($err['type']),
                    $err['message'],
                    $err['file'],
                    $err['line'],
                    $this->getFormattedTrace(debug_backtrace())
                );
            }
        };
        return $shutdownHandler;
    }

    /**
     * Return array of formatted back trace lines
     * @param array $trace
     * @return array
     */
    private function getFormattedTrace(array $trace): array
    {
        $formattedTrace = [];
        $traceIndex = count($trace);

        foreach ($trace as $record) {
            $traceLine = '#' . $traceIndex . ' called by \'' . $record['function'] . '\'';
            if (isset($record['class'])) {
                $traceLine .= ', class \'' . $record['class'] . '\'';
            }
            if (isset($record['file'], $record['line'])) {
                $traceLine .= ' in file \'' . $record['file'] . ' (on line: ' . $record['line'] . ')\'';
            }
            $traceIndex--;
            $formattedTrace[] = $traceLine;
        }

        return $formattedTrace;
    }

    /**
     * Process error output
     * @param string $errType
     * @param string $message
     * @param string $file
     * @param int $line
     * @param array $trace
     */
    private function outputError(string $errType, string $message, string $file, int $line, array $trace): void
    {
        $this->logError($errType, $message, $file, $line, $trace);

        if ($this->silent && !in_array($errType, $this->silentIngoreErrors)) {
            echo $this->silentPageContent;
            exit;
        }

        if (!$this->silent) {
            echo $this->htmlError($errType, $message, $file, $line, $trace);
            exit;
        }
    }

    /**
     * Return formatted error to html
     * @param string $errType
     * @param string $message
     * @param string $file
     * @param int $line
     * @param array $trace
     * @return string
     */
    private function htmlError(string $errType, string $message, string $file, int $line, array $trace): string
    {
        $html = '<h1>' . $errType . '</h1>';
        $html .= '<b>' . $message . '</b><br/><br/>';
        $pos = strrpos($file, '/');
        $html .= substr($file, 0, $pos + 1) . '<b>' . substr($file, $pos + 1, strlen($file)) . '</b> ';
        $html .= '(on line: <b>' . $line . '</b>)<br/><br/>';
        if (count($trace) > 0) {
            $html .= 'Trace:<br/>';
            foreach ($trace as $traceLine) {
                $html .= $traceLine . '<br/>';
            }
        }
        return $html;
    }

    /**
     * Log error using the error_log or Log
     * @param string $errType
     * @param string $message
     * @param string $file
     * @param int $line
     * @param array $trace
     */
    private function logError(string $errType, string $message, string $file, int $line, array $trace): void
    {
        $msg = '[' . $errType . '] ' . $message . ' in ' . $file . ' on line ' . $line;

        if ($this->log) {
            // Note: log levels aren't php error types, log levels reflect PSR-3

            // Set default log level
            $level = $this->errToLogDefLevel;

            // Set log level by error type
            if (isset($this->errToLogLevel[$errType])) {
                $level = $this->errToLogLevel[$errType];
            }

            $this->log->log($level, $msg, [], $trace);
            $this->log->write();
        }

        if (!$this->log) {
            error_log($msg);
        }
    }
}
