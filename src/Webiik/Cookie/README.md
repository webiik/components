<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

Cookie
======
The Cookie provides safe way to work with cookies.

Installation
------------
```bash
composer require webiik/cookie
```

Example
-------
```php
$cookie = new \Webiik\Cookie\Cookie();
$cookie->setCookie('foo', 'bar');
if ($cookie->isCookie('foo')) {
    echo 'Cookie foo has value: ' . $cookie->getCookie('foo');
}
$cookie->delCookie('foo');
```

Configuration
-------------
### setDomain
```php
setDomain(string $domain): void
```
**setDomain()** sets the (sub)domain that the cookie is available to.
```php
$cookie->setDomain('mydomain.tld');
```

### setUri
```php
setUri(string $uri): void
```
**setUri()** sets the path on the server in which the cookie will be available on.
```php
$cookie->setUri('/');
```

### setSecure
```php
setSecure(bool $bool): void
```
**setSecure()** indicates that the cookie should only be transmitted over a secure HTTPS connection from the client. The default value is **FALSE**.
```php
$cookie->setSecure(true);
```

### setHttpOnly
```php
setHttpOnly(bool $bool): void
```
**setHttpOnly()** indicates that the cookie should only be accessible through the HTTP protocol. The default value is **FALSE**.
```php
$cookie->setHttpOnly(true);
```

Adding
------
### setCookie
```php
setCookie(string $name, string $value = '', int $expire = 0, string $uri = '', string $domain = '', bool $secure = false, bool $httponly = false): bool
```
**setCookie()** sets a cookie to be sent along with the rest of the HTTP headers.
```php
$cookie->setCookie('foo', 'bar');
```

Check
-----
### isCookie
```php
isCookie(string $name): bool
```
**isCookie()** determines if a cookie is set. Returns **TRUE** if cookie exists.
```php
$cookie->isCookie('foo');
```

Getting
-------
### getCookie
```php
getCookie(string $name): string
```
**getCookie()** gets a cookie by **$name** and returns its value.
```php
$cookie->getCookie('foo');
```

Deletion
--------
### delCookie
```php
delCookie($name): void
```
**delCookie()** removes a cookie by **$name**.
```php
$cookie->delCookie('foo');
```

### delCookies
```php
delCookies(): void
```
**delCookies()** removes all cookies. 
```php
$cookie->delCookies();
```

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues