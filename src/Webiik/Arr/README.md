<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

Arr
===
The Arr provides dot notation for PHP arrays.

Installation
------------
```bash
composer require webiik/arr
```

Example
-------
```php
$array = [];
$arr = new \Webiik\Arr\Arr();
$arr->set('dot.notation.key', ['key' => 'val'], $array);
```

Adding
------
### set
```php
set(string $key, $val, array &$array): void
```
**set()** sets value **$val** to **$array** under (dot notation) **$key**.
```php
$arr->set('dot.notation.key', ['key' => 'val'], $array);
```

### add
```php
add(string $key, $val, array &$array): void
```
**add()** ads value **$val** to **$array** under (dot notation) **$key**.
```php
$arr->add('dot.notation.key', 'val', $array);
```

Check
-----
### isIn
```php
isIn(string $key, array $array): bool
```
**isIn()** determines if **$key** is set in array and if its value is not **NULL**.
```php
$arr->isIn('dot.notation.key', $array)
```

Getting
-------
### get
```php
get(string $key, array $array)
```
**get()** gets value from **$array** by (dot notation) **$key**.
```php
$arr->get('dot.notation.key', $array)
```

Deletion
--------
### delete
```php
delete(string $key, array &$array): void
```
**delete()** removes value from **$array** by (dot notation) **$key**.
```php
$arr->delete('dot.notation.test', $array);
```

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues