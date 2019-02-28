<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

Data
====
The Data is simple, read/write-only, key-value data container.

Installation
------------
```bash
composer require webiik/data
```

Example
-------
```php
$dataContainer = new \Webiik\Data\Data();
$dataContainer->set('foo', 'bar');
```

Adding
------
### set
```php
set(string $key, $data): void
```
**set()** sets **$data** to the container under given **$key**.
```php
$dataContainer->set('foo', 'bar');
```

Check
-----
### isIn
```php
isIn(string $key): bool
```
**isIn()** determines if **$key** is set in container and if its value is not **NULL**. 
```php
$dataContainer->isIn('foo');
```

Getting
-------
### get
```php
get(string $key)
```
**get()** gets data from the container by **$key**.
```php
$dataContainer->get('foo');
```

### getAll
```php
getAll(): array
```
**getAll()** returns key-value array with all data stored in the container.
```php
$dataContainer->getAll();
```

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues
