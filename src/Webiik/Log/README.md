<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

Log
===
The Log provides simple solution for advanced logging.

Installation
------------
```bash
composer require webiik/log
```

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
### addLogger
```php
addLogger(callable $factory): Logger
```
**addLogger()** creates new **Logger** and injects **$factory** into it. Adds created Logger to **Log** and returns it. To process logs you have to add some Logger(s) to Log. Log comes with 3 optional loggers: ErrorLogger, FileLogger and MailLogger.
```php
$log->addLogger(function () {
    return new \Webiik\Log\Logger\ErrorLogger();
});
```
**Write Custom Logger**

You can write your custom logger. Only thing you have to do is to implement `Webiik\Log\Logger\LoggerInterface`.   
```php
// CustomLogger.php
declare(strict_types=1);

use Webiik\Log\Message;

class CustomLogger implements Webiik\Log\Logger\LoggerInterface
{
    public function write(Message $message): void
    {
        // Process Message...
    }
}
```

Messages
--------
### add
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
Adds **Message** to **Log**. Added Messages are not written until the method **write()** is called. The **message** may contain {placeholders} which will be replaced with values from the **context** array. It return **Message**.
```php
$log->info('Hello {name}!', ['name' => 'Dolly!']);
```
### write 
```php
write(): void
```
**write()** removes all added Messages and writes them using the associated loggers.
```php
$log->write();
```
### setData
```php
Message->setData(array $data): Message
```
**setData()** adds extra data to your Message.
```php
$log->info('Hello Dolly!')->setData(['greeter' => 'Molly']);
```

Groups
------
### setGroup
```php
Logger->setGroup(string $group): Logger
Message->setGroup(string $group): Message
```
**setGroup()** adds Logger to positive group. Every logger and log message can belong to one or more positive group. When logger belongs to some group(s) then it logs only messages belonging to same group(s).
```php
// This logger logs only log messages belonging to 'error' group
$log->addLogger(function () {
    return new \Webiik\Log\Logger\ErrorLogger();
})->setGroup('error');

// Add some log messages
$log->info('Some info.');
$log->warning('Some error.')->setGroup('error');
```

### setNegativeGroup
```php
Logger->setNegativeGroup(string $group): Logger
Message->setGroup(string $group): Message
```
**setNegativeGroup()** adds Logger to negative group. Every logger can belong to one or more negative group. When logger belongs to some negative group(s) then it doesn't log messages belonging to same group(s).
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
### setLevel
```php
Logger->setLevel(string $level): Logger
```
**setLevel()** sets Logger to write only Messages with certain [PSR-3][3] log level.
```php
// This logger logs messages from all groups but only with log level 'info'
$log->addLogger(function () {
    return new \Webiik\Log\Logger\FileLogger();
})->setLevel('info');
```

Silent mode
-----------
### setSilent
```php
setSilent(bool $silent): void
```
**setSilent()** configures **Log** to skip failed Loggers. In silent mode failed loggers don't stop code execution, instead of it these incidents are logged with other loggers. The default value is **FALSE**.
```php
$log = setSilent(true);
```

Resources
---------
* [Webiik framework][1]
* [Report issues][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/webiik/issues
[3]: https://www.php-fig.org/psr/psr-3/
