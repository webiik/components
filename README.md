<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

Session
=======
The Session provides safe way to work with sessions.

Installation
------------
```bash
composer require webiik/session
```

Example
-------
```php
$session = new \Webiik\Session\Session();
$session->setToSession('foo', 'bar');
if ($session->isInSession('foo')) {
    echo 'Session foo has value: ' . $session->getFromSession('foo');
}
$session->delFromSession('foo');
```
> **NOTICE:** If you can, don't forget to call [session_write_close()][1] before time intensive operations. Otherwise users of your app can experience serious lags.

Configuration
-------------
### setDomain
```php
setDomain(string $domain): void
```
**setDomain()** sets the (sub)domain that the session cookie is available to.
```php
$session->setDomain('mydomain.tld');
```

### setUri
```php
setUri(string $uri): void
```
**setUri()** sets the path on the server in which the session cookie will be available on.
```php
$session->setUri('/');
```

### setSecure
```php
setSecure(bool $bool): void
```
**setSecure()** indicates that the session cookie should only be transmitted over a secure HTTPS connection from the client. The default value is **FALSE**.
```php
$session->setSecure(true);
```

### setHttpOnly
```php
setHttpOnly(bool $bool): void
```
**setHttpOnly()** indicates that the session cookie should only be accessible through the HTTP protocol. The default value is **FALSE**.
```php
$session->setHttpOnly(true);
```

### setSessionName
```php
setSessionName(string $name): void
```
**setSessionName()** sets the name of the session which is used as cookie name. The default value is **PHPSESSID**.  
```php
$session->setSessionName('mySessionName');
```

### setSessionDir
```php
setSessionDir(string $path): void
```
**setSessionDir()** defines the argument which is passed to the save handler. If you choose the default files handler, this is the path where the files are created.
```php
$session->setSessionDir(__DIR__ . '/tmp');
```

### setSessionGcProbability
```php
setSessionGcProbability(int $sessionGcProbability): void
```
**setSessionGcProbability()** in conjunction with `setSessionGcDivisor()` is used to manage probability that the gc (garbage collection) routine is started. The default value is 1.
```php
$session->setSessionGcProbability(1);
```

### setSessionGcDivisor
```php
setSessionGcDivisor(int $sessionGcDivisor): void
```
**setSessionGcDivisor()** coupled with `setSessionGcProbability()` defines the probability that the gc (garbage collection) process is started on every session initialization. The probability is calculated by using GsProbability/GcDivisor, e.g. 1/100 means there is a 1% chance that the GC process starts on each request. The default value of GcDivisor is 100.
```php
$session->setSessionGcDivisor(100);
```

### setSessionGcLifetime
```php
setSessionGcLifetime(int $sec): void
```
**setSessionGcLifetime()** specifies the number of seconds after which data will be seen as 'garbage' and potentially cleaned up. Garbage collection may occur during session start (depending on `setSessionGcProbability()` and `setSessionGcDivisor()`).
```php
$session->setSessionGcLifetime(1440);
```
> **Note:** If different scripts have different values of gcLifetime but share the same place for storing the session data then the script with the minimum value will be cleaning the data. In this case, use this directive together with `setSessionDir()`.

Adding
------
### setToSession
```php
setToSession(string $key, $value): void
```
**setToSession()** sets **$value** to the session under given **$key**.
```php
$session->setToSession('foo', 'bar');
```

### addToSession
```php
addToSession(string $key, $value): void
```
**addToSession()** ads **$value** to the session under given **$key**.
```php
$session->addToSession('foo', 'bar');
```

### sessionStart
```php
sessionStart(): bool
```
**sessionStart()** starts the session. Use it when you need to work directly with **$_SESSION** global. 
```php
$session->sessionStart();
```

### sessionRegenerateId
```php
sessionRegenerateId(): void
```
**sessionRegenerateId()** replaces the current session id with a new one, and keeps the current session information. Also the original session is kept.
```php
$session->sessionRegenerateId();
```

Check
-----
### isInSession
```php
isInSession(string $key): bool
```
**isInSession()** determines if **$key** is set in session and if its value is not **NULL**.
```php
$session->isInSession('foo');
```

Getting
-------
### getFromSession
```php
getFromSession(string $key)
```
**getFromSession()** gets value from the session by **$key**.
```php
$session->getFromSession('foo');
```

### getAllFromSession
```php
getAllFromSession()
```
**getAllFromSession()** returns all values stored in the session. 
```php
$session->getAllFromSession();
```

Deletion
--------
### delFromSession
```php
delFromSession(string $key): void
```
**delFromSession()** removes value from the session by **$key**.
```php
$session->delFromSession('foo');
```

### dellAllFromSession
```php
dellAllFromSession(): void
```
**dellAllFromSession()** removes all values from the session.  
```php
$session->dellAllFromSession();
```

### sessionDestroy
```php
sessionDestroy(): bool
```
**sessionDestroy()** removes all values from the session and un-sets the session.
```php
$session->sessionDestroy();
```

Resources
---------
* [Webiik framework][1]
* [Report issues][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/webiik/issues
[3]: http://php.net/manual/en/function.session-write-close.php#96982