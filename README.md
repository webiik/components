<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

Attempts
========
The Attempts provides common interface for user actions monitoring.

Installation
------------
```bash
composer require webiik/attempts
```

Example
-------
> The following example expects you have already written [your custom storage](#setstorage).
```php
// Instatiate Attempts
$attempts = new \Webiik\Attempts\Attempts();

// Set storage (you have to write your own storage)
$attempts->setStorage(function() {
    return new \Webiik\Attempts\YourCustomStorage();
});

// Store user login attempt
$attempts->write('login', $attempts->getIp());

// Get user login attempts within the last hour
$startTimestamp = time() - 60 * 60;
$loginAttempts = $attempts->readByIp('login', $attempts->getIp(), $startTimestamp);

if(count($loginAttempts) > 10) {
    // E.g. Temporary prevent user to log in
}

// Delete expired login attempts with probability 2/100
$attempts->delete('login', $startTimestamp, 2);
```

Configuration
-------------
### setStorage
```php
setStorage(callable $factory): void
```
**setStorage()** sets storage factory. Storage writes, reads and deletes user attempts. Every storage must implement [StorageInterface](StorageInterface.php). Take a look at [StorageInterface](StorageInterface.php) to get more info. Keep on mind you have to write your own storage. For example, you can write storage that uses MySQL database to write, read and delete user attempts.
```php
$attempts->setStorage(function() {
    return new \Webiik\Attempts\YourCustomStorage();
});
```

User Identifier
---------------
### getIp
```php
getIp(): string
```
**getIp()** returns user IP address.
```php
$ip = $attempts->getIp();
```

Attempts
--------
### write
```php
write(string $label, string $ip, string $hash = ''): void
```
**write()** writes user attempt to storage.

**Parameters**
* **label** label representing user action e.g. login 
* **ip** user IP address (simple user identifier)
* **hash** advanced user identifier e.g. hash from user IP, OS, browser language and installed fonts 
```php
$attempts->write('login', $ip, $hash);
```

### read
```php
read(string $label, string $ip, string $hash, int $startTimestamp = 0): array
```
**read()** reads user attempts from storage by **label**, **hash** and **ip** starting from **startTimestamp**.
```php
$startTimestamp = time() - 60 * 60;
$loginAttempts = $attempts->read('login', $ip, $hash, $startTimestamp);
```

### readByIp
```php
readByIp(string $label, string $ip, int $startTimestamp = 0): array
```
**readByIp()** reads user attempts from storage by **label** and **ip** starting from **startTimestamp**.
```php
$startTimestamp = time() - 60 * 60;
$loginAttempts = $attempts->readByIp('login', $ip, $startTimestamp);
```

### readByHash
```php
readByHash(string $label, string $hash, int $startTimestamp = 0): array
```
**readByHash()** reads user attempts from storage by **label** and **hash** starting from **startTimestamp**.
```php
$startTimestamp = time() - 60 * 60;
$loginAttempts = $attempts->readByHash('login', $hash, $startTimestamp);
```

### delete
```php
delete(string $label, int $timestamp, int $probability = 1): void
```
**delete()** deletes user attempts from storage by the specific **label**, older than the **timestamp**, with default **probability** 1/100.
```php
$olderThanTimestamp = time() - 60 * 60;
$attempts->delete('login', $olderThanTimestamp);
```

### deleteAll
```php
deleteAll(int $timestamp, int $probability = 1): void
```
**deleteAll()** deletes user attempts from storage older than the **timestamp**, with default **probability** 1/100.
```php
$olderThanTimestamp = time() - 60 * 60;
$attempts->delete($olderThanTimestamp);
```

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues