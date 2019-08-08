<?php
declare(strict_types=1);

namespace Webiik\Error;

class Error
{
    /**
     * Function with underlying log service
     * If no function is set, error_log function will be used instead
     *
     * Function is injected with the following parameters:
     * string $level, string $message, array $data
     *
     * @var callable
     */
    private $logService;

    /**
     * @var string
     */
    private $errToLogDefLevel = 'warning';

    /**
     * @var array
     */
    private $errToLogLevel = [
        'Exception' => 'error',
        'E_ERROR' => 'error',
    ];

    /**
     * @var bool
     */
    private $silent = false;

    /**
     * The following error types will not halt the app in silent mode
     * @link http://php.net/manual/en/errorfunc.constants.php
     * @var array
     */
    private $silentIgnoreErrors = [
        'E_NOTICE',
        'E_USER_NOTICE',
        'E_DEPRECATED',
        'E_USER_DEPRECATED',
    ];

    /**
     * @var string
     */
    private $silentPageContent = '';

    /**
     * Indicates if silent page is already shown
     * @var bool
     */
    private $silentPageShown = false;

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
     * @param callable $function
     */
    public function setLogService(callable $function): void
    {
        $this->logService = $function;
    }

    /**
     * Set default log level of php errors when using Log
     * Will be used for all php errors without associated log level
     * @param string $level
     */
    public function setErrLogDefLevel(string $level): void
    {
        $this->errToLogDefLevel = $level;
    }

    /**
     * Set log level of specific php error when using Log
     * @param array $assocArr
     */
    public function setErrLogLevel(array $assocArr): void
    {
        $this->errToLogLevel = $assocArr;
    }

    /**
     * Determine if silent mode is used
     * In silent mode, errors are logged but not displayed
     * @param bool $bool
     */
    public function setSilent(bool $bool): void
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
     * @param array $arr
     */
    public function setSilentIgnoreErrors(array $arr): void
    {
        $this->silentIgnoreErrors = $arr;
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
        return function ($exception) {
            $this->outputError(
                'Exception',
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                $this->getFormattedTrace($exception->getTrace())
            );
        };
    }

    /**
     * Return error handler function
     * @return callable
     */
    private function errorHandler(): callable
    {
        return function ($errno, $errstr, $errfile, $errline) {
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
    }

    /**
     * Return shutdown handler function
     * @return callable
     */
    private function shutdownHandler(): callable
    {
        return function () {
            $err = error_get_last();
            if ($err) {
                // Separate message
                preg_match('/.+/', $err['message'], $match);
                $msg = $match ? $match[0] : '';

                // Separate backtrace
                preg_match('/trace:(.+)/s', $err['message'], $match);
                if (isset($match[1]) && $match[1]) {
                    $trace = preg_split('/\s#\d+/', $match[1]);
                    $trace = is_array($trace) ? $trace : [];
                    unset($trace[0]);
                    $traceIndex = count($trace);
                    foreach ($trace as $key => $traceLine) {
                        $trace[$key] = '#' . $traceIndex . ' ' . $traceLine;
                        $traceIndex--;
                    }
                } else {
                    $trace = $this->getFormattedTrace(debug_backtrace());
                }

                $this->outputError(
                    $this->getErrType($err['type']),
                    $msg,
                    $err['file'],
                    $err['line'],
                    $trace
                );
            }
        };
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
     * @throws \Exception
     */
    private function outputError(string $errType, string $message, string $file, int $line, array $trace): void
    {
        $exit = false;

        if ($this->silent && !in_array($errType, $this->silentIgnoreErrors)) {

            // We need silentPageShown indicator to prevent multiple output of silent error page,
            // because exit; doesn't stop shut down functions execution
            if (!$this->silentPageShown) {
                echo $this->silentPageContent;
                $this->silentPageShown = true;
            }
            $exit = true;
        }

        if (!$this->silent) {
            echo $this->htmlError($errType, $message, $file, $line, $trace);
            $exit = true;
        }

        $this->logError($errType, $message, $file, $line, $trace);

        if ($exit) {
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
     * @throws \Exception
     */
    private function logError(string $errType, string $message, string $file, int $line, array $trace): void
    {
        $msg = '[' . $errType . '] ' . $message . ' in ' . $file . ' on line ' . $line;

        // Note: log levels aren't php error types, log levels reflect PSR-3

        if ($this->logService === null) {
            error_log($msg);
        } else {
            // Set default log level
            $level = $this->errToLogDefLevel;

            // Set log level by error type
            if (isset($this->errToLogLevel[$errType])) {
                $level = $this->errToLogLevel[$errType];
            }

            // Prepare additional data to log
            $data = [
                'error type' => $errType,
                'file' => $file,
                'line' => $line,
                'error message' => $message,
                'trace' => $trace,
            ];

            // Add and write error log
            $logService = $this->logService;
            $logService($level, $msg, $data);
        }
    }
}
