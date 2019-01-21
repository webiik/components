<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

Arr
===
The Arr provides dot notation for PHP arrays.

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
**set()** sets the value **$val** to the **$array** with given (dot notation) **$key**.
```php
$arr->set('dot.notation.key', ['key' => 'val'], $array);
```

### add
```php
add(string $key, $val, array &$array): void
```
**add()** ads the value **$val** to the **$array** with given (dot notation) **$key**.
```php
$arr->add('dot.notation.key', 'val', $array);
```

Checking
--------
### isIn
```php
isIn(string $key, array $array): bool
```
**isIn()** checks the array for the item with given (dot notation) **$key**. If the item is found, it returns **true**.
```php
$arr->isIn('dot.notation.key', $array)
```

Getting
-------
### get
```php
get(string $key, array $array)
```
**get()** gets the item from the **$array** by given (dot notation) **$key**.
```php
$arr->get('dot.notation.key', $array)
```

Deleting
--------
### delete
```php
delete(string $key, array &$array): void
```
**delete()** removes the item from the **$array** by given (dot notation) **$key**.
```php
$arr->delete('dot.notation.test', $array);
```

Resources
---------
* [Webiik framework][1]
* [Report issues][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/webiik-components/issues