<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

Error
=====
The Error provides more control over displaying and logging PHP errors.

Installation
------------
```bash
composer require webiik/error
```

Example
------- 
```php
$error = new \Webiik\Error\Error();
```

Silent mode
-----------
### silent
```php
silent(bool $bool): void
```
**silent()** activates or deactivates silent mode. When Error is not in silent mode it halts code execution on every error and then it displays error message. In silent mode instead of displaying error message a custom error page is shown and some errors can be completely ignored.
```php
$error->silent(true);
```
### setSilentPageContent 
```php
setSilentPageContent(string $string): void
```
**setSilentPageContent()** sets custom error page to be shown when error occurs in silent mode.
```php
$error->setSilentPageContent('<h1>Ups! Kitten lost!</h1>');
```
### setSilentIgnoreErrors
```php
setSilentIgnoreErrors(array $arr): void
```
**setSilentIgnoreErrors()** sets an array of [error constants][3] that will be ignored in silent mode - no custom error page will be shown and code execution will not stop.
```php
$error->setSilentIgnoreErrors([
    'E_NOTICE',
    'E_USER_NOTICE',
    'E_DEPRECATED',
    'E_USER_DEPRECATED',
]);
```

Logging
-------
### setErrLogDefLevel 
```php
setErrLogDefLevel(string $level): void
```
**setErrLogDefLevel()** associates PSR-3 log level to all PHP error constant. The default value is _warning_.
```php 
$error->setErrLogDefLevel('error');
```
> Note: Please keep on mind that [PHP error constant][3] (eg. E_NOTICE) and [PSR-3 log level][4] (eg. notice) are two different things and there is no connection between them. 

### setErrLogLevel
```php
setErrLogLevel(array $assocArr): void
```
**setErrLogLevel()** associates PSR-3 log level to specific PHP error constant. Default values are shown in the example below.
```php
$error->setErrLogLevel([
    'Exception' => 'error',
    'E_ERROR' => 'error',
]);
```

### setLogService
```php
setLogService(callable $function): void
```
**setLogService()** sets custom logger logging errors. **function** is injected with the following parameters: **string $level, string $message, array $data**. By default all PHP errors are logged with **error_log()**.
```php
$error->setLogService(function ($level, $message, $data) {
    // $level - PSR-3 log level
    // $message - Re-fromatted error message
    // $data - ['error type' => $errType, 'file' => $file, 'line' => $line, 'error message' => $message, 'trace' => $trace]
    // Your custom logger...
});
```

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues
[3]: http://php.net/manual/en/errorfunc.constants.php
[4]: https://www.php-fig.org/psr/psr-3/