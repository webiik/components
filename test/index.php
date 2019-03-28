<?php
$before = microtime(true);

require __DIR__ . '/../vendor/autoload.php';

// Common settings
$silent = false;

// Create Err to init custom error reporting
$error = new \Webiik\Error\Error();

// Use silent error reporting
$error->setSilent($silent);

// These error types will not stop code executing in silent mode
$error->setSilentIgnoreErrors([
    'E_WARNING',
    'E_CORE_WARNING',
    'E_COMPILE_WARNING',
    'E_USER_WARNING',
    'E_NOTICE',
    'E_USER_NOTICE',
    'E_DEPRECATED',
    'E_USER_DEPRECATED',
]);

// Create app container
$container = new \Webiik\Container\Container();

// Add custom log service for logging errors
$error->setLogService(function ($level, $message, $data) use (&$container) {
    /** @var \Webiik\Log\Log $log */
    $log = $container->get('\Webiik\Log\Log');
    $log->log($level, $message)->setData($data)->setGroup('error');
    $log->write();
});

require __DIR__ . '/test.php';

echo 'Peak memory usage: ' . (memory_get_peak_usage() / 1000000) . ' MB';
echo '<br/>End memory usage: ' . (memory_get_usage() / 1000000) . ' MB';

$after = microtime(true);
echo '<br/><br/>Execution time: ' . ($after - $before) . ' sec';
