<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

Data
====
The Data is simple, read/write-only, key-value data container.

Example
-------
```php
$dataContainer = new \Webiik\Data\Data();
$dataContainer->set('key', 'val');
```

Adding
------
#### set
```php
set(string $key, $data): void
```
**set()** sets an item to the container with given **$key**.
```php
$dataContainer->set('key', '$val');
```

Checking
--------
#### isIn
```php
isIn(string $key): bool
```
**isIn()** checks the container for the item with given **$key**. If the item is found, it returns **true**. 
```php
$dataContainer->isIn('key');
```

Getting
-------
#### get
```php
get(string $key)
```
**get()** gets the item from the container by given **$key**.
```php
$dataContainer->get('key');
```

#### getAll
```php
getAll(): array
```
**getAll()** returns key-value array with all items stored in the container.
```php
$dataContainer->getAll();
```

Resources
---------
* [Webiik framework][1]
* [Report issues][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/webiik-components/issues
