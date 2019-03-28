<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-3-brightgreen.svg"/>
</p>

Login
=====
The Login manages the user login state. It supports:
* permanent login
* automatic logout
* login namespaces

> **Note:** This class is meant to be used after the successful user authentication and authorization.

Installation
------------
```bash
composer require webiik/login
```

Example
-------
```php
$login = new \Webiik\Login\Login($token, $cookie, $session);

// Log-in the user with id 1
$login->login(1);

// Check if the user is logged in 
if ($login->isLogged()) {
    echo 'The user id ' . $login->getUserId() . ' is logged.';
} else {
    echo 'The user is not logged.';
}

// Log the user out
$login->logout();
```

Summary
-------
* [Basic Login](#basic-login)
* [Permanent Login](#permanent-login)
* [Login Check](#login-check)
* [Logout](#logout)

Basic Login
-----------
### setSessionKey
```php
setSessionKey(string $sessionKey): void
```
**setSessionKey()** sets the key of login state stored in PHP session. This key holds the [**uid**](#login) value. The default value of the key is **logged**.
```php
$login->setSessionName('logged');
```

### setNamespace
```php
setNamespace(string $name): void
```
**setNamespace()** sets login namespace. If you want to make the login valid only for the specific part of your app, use the login namespace. Imagine you have a multilingual app and you want to make a separate account for every language - this is the situation when the login namespace can help.
```php
$login->setNamespace('en');
```

### login
```php
login($uid, bool $permanent = false): void
``` 
**login()** logs the user in. Login() writes login state to PHP session. If permanent login is not set, login is valid until the user closes the browser or the [PHP session][3] expires.

**Parameters**
* **uid** user unique identifier to be stored in PHP session
* **permanent** indicates if to use permanent login. Read more in [permanent login](#permanent-login) section.
```php
// Log in the user with id 1
$login->login(1);
```
```php
// Permanently log in the user with id 1
$login->login(1, true);
```

Permanent Login
---------------
### setPermanentCookieName
```php
setPermanentCookieName(string $name): void
```
**setPermanentCookieName()** sets the name of cookie where the permanent login information is stored at the users' computer. The default value is **PC**.
```php
$login->setPermanentCookieName('PC');
```

### setPermanentLoginStorage
>**Note:** To start using the permanent login, it's required to set permanent login storage. 
```php
setPermanentLoginStorage(callable $factory, int $days = 30): void
```
**setPermanentLoginStorage()** sets the factory of permanent login storage and the time validity of permanent login data.

**Parameters**
* **factory** factory creates object implementing **[StorageInterface](Storage/StorageInterface.php)**
* **days** how many days to keep permanent cookie and data in storage when user is not active
 
Permanent login saves the permanent login data to the user's computer (cookie) and to the server. Storage is here to solve the server part and to make storing data flexible. Out of the box, the Login class comes with the [FileStorage](Storage/FileStorage.php), it saves login information to the disk at the server. However, you can write your own storage.

Example of using the file storage:
```php
$login->setPermanentLoginStorage(function () {
    $fs = new \Webiik\Login\Storage\FileStorage();
    $fs->setPath(__DIR__ . '/tmp/permanent');
    return $fs;
});
```

**Write Custom Storage**

You can write your custom storage. Only thing you have to do is to implement **[StorageInterface](Storage/StorageInterface.php)**.

Login Check
-----------
### isLogged
```php
isLogged(): bool
```
**isLogged()** checks if user is logged in.
```php
if ($login->isLogged()) {
    echo 'The user id ' . $login->getUserId() . ' is logged.';
} else {
    echo 'The user is not logged.';
}
```

Logout
------
### logout 
```php
logout(): void
```
**logout()** logs the user out.
```php
$login->logout();
```

### setAutoLogoutTime
```php
setAutoLogoutTime(int $sec): void
```
**setAutoLogoutTime()** sets the time in seconds to auto logout the user on inactivity between two requests. The default value is 0 - no automatic logout. Automatic logout is ignored when user is logged permanently.
```php
// Set the automatic logout after 5 minutes of inactivity between two requests
$login->setAutoLogoutTime(5 * 60);
```
**Warning:** [Update the time](#updateautologoutts) of last user activity with every http request to make auto logout feature working properly.

### updateAutoLogoutTs
```php
updateAutoLogoutTs(): void
```
**updateAutoLogoutTs()** updates time of last users' activity stored in the session.
```php
// Never call this before isLogged or isAuthorized, it would 
// let to the situation, that the user was never logged out
$login->updateAutoLogoutTs();
```

### getLogoutReason 
```php
getLogoutReason(): void
```
**getLogoutReason()** returns logout reason.
```php
if ($login->getLogoutReason() == $login::MANUAL) {
    // manual logout
} elseif ($login->getLogoutReason() == $login::AUTO) {
    // auto logout due inactivity
}
```

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues
[3]: https://github.com/webiik/webiik/blob/master/src/Webiik/Session/README.md