<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

Error
=====
The Error provides more control over displaying and logging PHP errors.

Example
------- 
```php
$error = new \Webiik\Error\Error();
```
Silent mode
-----------
When Error is not in silent mode it halts code execution on every error and then it displays error message. In silent mode instead of displaying error message a user friendly error page is shown and some errors can be completely ignored.
  
#### Activate & deactivate silent mode
```php
silent(bool $bool): void
```
```php
$error->silent(true); // active
```
#### Set error page
Set content to be shown when error occurs in silent mode. 
```php
setSilentPageContent(string $string): void
```
```php
$error->setSilentPageContent('<h1>Ups! Kitten lost!</h1>');
```
#### Set error constants to ignore
Set an array of [error constants][3] that will be ignored is silent mode.
```php
setSilentIgnoreErrors(array $arr): void
```
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
All PHP errors are logged by default.

#### Set default log level
Set default PSR-3 log level of all PHP errors. 
```php
setErrLogDefLevel(string $level): void
```
```php
// All PHP errors will be logged with log level 'error' 
$error->setErrLogDefLevel('error');
```
> Note: Please keep on mind that [PHP error constant][3] (eg. E_NOTICE) and [PSR-3 log level][4] (eg. notice) are two different things and there is no equal between them. 

#### Set custom log level
You can set that some PHP error constants and types will be logged with specific PSR-3 log level.
```php
setErrLogLevel(array $assocArr): void
```
```php
$error->setErrLogLevel([
    'Exception' => 'error',
    'E_ERROR' => 'error',
]);
```

#### Set custom logger
Set custom log function for logging errors. Function is injected with the following parameters: `string $level`, `string $message`, `array $data`
```php
setLogService(callable $function): void
```
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
* [Report issues][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/webiik-components/issues
[3]: http://php.net/manual/en/errorfunc.constants.php
[4]: https://www.php-fig.org/psr/psr-3/