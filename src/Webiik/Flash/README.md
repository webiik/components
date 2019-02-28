<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-1-brightgreen.svg"/>
</p>

Flash
=====
The Flash provides multilingual flash notifications.

Installation
------------
```bash
composer require webiik/flash
```

Example
-------
```php
$flash = new \Webiik\Flash\Flash($session);
$flash->addFlashCurrent('inf', 'Hello {name}', ['name' => 'Dolly']);
$flash->addFlashNext('inf', 'Hello {name}', ['name' => 'Molly']);
print_r($flash->getFlashes()); // Array ([inf] => Array ([0] => Hello Dolly ))
```
Next request:
```php
$flash = new \Webiik\Flash\Flash($session);
print_r($flash->getFlashes()); // Array ([inf] => Array ([0] => Hello Molly ))
```

Configuration
-------------
### setLang
```php
setLang(string $lang): void
```
**setLang()** sets current language of flash messages. The default value is **en**.
```php
$flash->setLang('en');
```

Adding
------
### addFlashCurrent
```php
addFlashCurrent(string $type, string $message, array $context = []): void
```
**addFlashCurrent()** adds flash message in current language to be displayed in current request. **$type** represents custom type of message e.g. inf, err, ok. The message may contain {placeholders} which will be replaced with values from the **$context** array. 
```php
$flash->addFlashCurrent('inf', 'Hello {name}', ['name' => 'Dolly']);
```

### addFlashNext
```php
addFlashNext(string $type, string $message, array $context = []): void
```
**addFlashNext()** adds flash message in current language to be displayed in next request. **$type** represents custom type of message e.g. inf, err, ok. The message may contain {placeholders} which will be replaced with values from the **$context** array.
```php
$flash->addFlashNext('inf', 'Hello {name}', ['name' => 'Molly']);
```

Getting
-------
### getFlashes
```php
getFlashes(): array
```
**getFlashes()** returns array of all messages to be displayed in current request and language.
```php
$flashMessages = $flash->getFlashes();
```

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues