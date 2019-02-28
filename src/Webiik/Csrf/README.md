<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-2-brightgreen.svg"/>
</p>

Csrf
====
The Csrf provides CSRF protection.

Installation
------------
```bash
composer require webiik/csrf
```

Example
-------
```php
$csrf = new \Webiik\Csrf\Csrf($token, $session);
$token = $csrf->create();

// Now send the $token to the next request, e.g. using $_POST...
```
In the next request validate token:
```php
$csrf = new \Webiik\Csrf\Csrf($token, $session);
if ($csrf->validate($_POST[$csrf->getName()])) {
    // CSRF token is valid
}
```

Configuration
-------------
### setName
```php
setName(string $name): void
```
**setName()** sets custom CSRF token name, the default name is 'csrf-token'. It is also the session key of CSRF token.
```php
$csrf->setName('my-csrf-token');
```
### setMax
```php
setMax(int $max): void
```
**setMax()** sets the maximum number of simultaneous CSRF tokens that can be stored in the session. The default number is 5. It means, for example, that user can open up to 5 CSRF protected forms at once. If this limit is exceeded, the method `create()` does not generate new CSRF token, but it returns the lastly generated token.

```php
$csrf->setMax(5);
```
> Save resources and never set too big number. 

Generating
----------
### create
```php
create(bool $safe = false): string
```
**create()** returns 16 characters long CSRF token and stores it in the session. If you want to generate safe tokens, set the **$safe** parameter to **true**.
```php
$csrfToken = $csrf->create();
```
> Safe tokens are slower to generate and require more resources.

Validation
----------
### validate
```php
validate(string $token, bool $safe): bool
```
**$validate()** validates **$token** to the all CSRF tokens stored in session. If **$token** is valid, it returns true and deletes valid token from session. If you want to use the timing-attack safe validation, set the **$safe** parameter to **true**. 
```php
$csrf->validate($token);
```
> Timing-attack safe validation is slower and requires more resources.

Resources
---------
* [Webiik framework][1]
* [Report issues][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/webiik/issues