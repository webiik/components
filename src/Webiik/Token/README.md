<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

Token
=====
The Token generates and compares tokens.

Installation
------------
```bash
composer require webiik/token
```

Example
-------
```php
$token = new \Webiik\Token\Token();
$secureToken = $token->generate();
if ($token->compare('vuefjsdfk', $secureToken)) {
    // Tokens are equal
}
```

Generating
----------
### generate
```php
generate($strength = 16): string
```
**generate()** returns safe token. By default the token is 32 characters long. It throws Exception when it was not possible to generate safe token. 
```php
try {
    $token->generate();
} catch (Exception $exception) {
    // Unable to generate strong token
}
```

### generateCheap 
```php
generateCheap($length = 32): string
```
**generateCheap()** returns cheap token. By default the token is 32 characters long. Cheap token is not safe, but is faster to generate.
```php
$token->generateCheap();
```

Comparison
----------
### compare
```php
compare(string $original, string $imprint): bool
```
**compare()** Compares two strings using the same time whether they're equal or not - Timing attack safe string comparison.
```php
$token->compare('known-string', 'user-string');
```
> Timing-attack safe comparison is slower than regular comparison.

Resources
---------
* [Webiik framework][1]
* [Report issues][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/webiik/issues