<p align="center">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

Log
===
The Log provides simple solution for advanced logging.

Example
-------
```php
$log = new \Webiik\Log\Log();
$log->addLogger(function () {
    return new \Webiik\Log\Logger\FileLogger();
});
$log->info('Hello {name}!', ['name' => 'Dolly!']);
$log->write();
```

Loggers
-------
#### Add Logger
To process logs you have to add some logger(s) to Log component. Log component comes with 3 optional loggers: ErrorLogger, FileLogger and MailLogger.
```php
addLogger(callable $factory): Logger
```
```php
$log->addLogger(function () {
    return new \Webiik\Log\Logger\ErrorLogger();
});
```
#### Write Custom Logger
There is only one rule to write custom logger. Your custom logger has to implement method `write(Message $message): void;` from the `LoggerInterface`.   

Log Messages
------------
#### Add Log Message
```php
emergency(string $message, array $context = []): Message
alert(string $message, array $context = []): Message
critical(string $message, array $context = []): Message
error(string $message, array $context = []): Message
warning(string $message, array $context = []): Message
notice(string $message, array $context = []): Message
info(string $message, array $context = []): Message
debug(string $message, array $context = []): Message
log(string $level, string $message, array $context = []): Message
```
```php
$log->info('Hello {name}!', ['name' => 'Dolly!']);
```
#### Write Log Messages
To process log messages using the loggers you have to call method `write`. Calling `write` also removes all log messages from Log. 
```php
write(): void
```
```php
$log->write();
```
#### Add Extra Data
If logger requires extra data you can add them to log message.
```php
$log->info('Hello Dolly!')->setData(['greeter' => 'Molly']);
```

Groups
------
#### Positive Groups 
Every logger and log message can belong to group(s). When logger belongs to some group(s) then it logs only messages belonging to same group(s).
```php
// This logger logs only log messages belonging to 'error' group
$log->addLogger(function () {
    return new \Webiik\Log\Logger\ErrorLogger();
})->setGroup('error');

// Add some log messages
$log->info('Some info.');
$log->warning('Some error.')->setGroup('error');
```
#### Negative Groups 
Every logger can belong to negative group(s). When logger belongs to some negative group(s) then it doesn't log messages belonging to same group(s).  
```php
// This logger doesn't log messages belonging to 'error' group
$log->addLogger(function () {
    return new \Webiik\Log\Logger\FileLogger();
})->setNegativeGroup('error');

// Add some log messages
$log->info('Some info.');
$log->warning('Some error.')->setGroup('error');
```

Levels
------
Every log message belongs to one of [PSR-3][3] log level. You can configure logger to log only certain log level of messages.
```php
// This logger logs messages from all groups but only with log level 'info'
$log->addLogger(function () {
    return new \Webiik\Log\Logger\FileLogger();
})->setLevel('info');
```

Silent mode
-----------
In silent mode failed loggers don't stop code execution, instead of it these incidents are logged with other loggers and failed loggers are skipped.
```php
setSilent(bool $silent): void
```
```php
$log = setSilent(true);
```

Resources
---------
* [Webiik framework][1]
* [Report issues][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/webiik-components/issues
[3]: https://www.php-fig.org/psr/psr-3/
