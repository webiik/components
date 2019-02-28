<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

Database
========
The Database is PDO connection container.

Installation
------------
```bash
composer require webiik/database
```

Example
-------
```php
$db = new \Webiik\Database\Database();
$db->add('main', 'mysql', 'localhost', 'webiik', 'root', 'root');
$pdo = $db->connect();
```

Settings
--------
### add
```php
add(string $name, string $driver, string $host, string $databaseName, string $user, string $password, array $options = [], array $commands = []): void
```
**add()** adds database connection credentials.

**Parameters**
* **name** name of current database connection. You will be able to get this connection by this name.
* **driver** [pdo driver](http://php.net/manual/en/pdo.drivers.php) 
* **host** host name e.g. localhost
* **databaseName** database name
* **user** database user name
* **password** database user password
* **options** array of [PDO options](http://php.net/manual/en/class.pdo.php)
* **commands** array of MySQL commands to execute after connecting to database. It's handy for setting time-zone, encoding etc.
```php
$db->add('main', 'mysql', 'localhost', 'webiik', 'root', 'root');
```

Connection
----------
### connect
```php
connect(string $name = ''): \PDO
```
**connect()** connects to database and return PDO object. When **name** is omitted it connects to first added database. 
```php
$pdo = $db->connect();
```

### disconnect
```php
disconnect(string $name = ''): void
```
**disconnect()** disconnects from database. When **name** is omitted it disconnects from first added database. 
```php
$db->disconnect();
```

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues