<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-3-brightgreen.svg"/>
</p>

Login
=====
The Login manages the user login state. It supports:
* permanent login
* automatic logout of inactive users
* user roles and actions
* login resolution (e.g. by lang, app part, ...)

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
* [User Roles and Action](#user-roles-and-action)
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

### setLoginSection
```php
setLoginSection(string $name): void
```
**setLoginSection()** sets login resolution. If you want to make the login valid only for the specific part of your app, use the login resolution. Imagine you have a multilingual app and you want to make a separate account for every language - this is the situation when the login resolution can help.
```php
$login->setLoginSection('en');
```

### login
```php
login($uid, bool $permanent = false, string $role = ''): void
``` 
**login()** logs the user in. Login() writes login state to PHP session. If permanent login is not set, login is valid until the user closes the browser or the [PHP session][3] expires.

**Parameters**
* **uid** user unique identifier to be stored in PHP session
* **permanent** indicates if to use permanent login. Read more in [permanent login](#permanent-login) section.
* **role** user role to be stored in PHP session. Read more in [user roles and actions](#user-roles-and-action) section.
```php
// Log in the user with id 1
$login->login(1);
```
```php
// Permanently log in the user with id 1
$login->login(1, true);
```
```php
// Log in the user with id 1 and role contributor
$login->login(1, 'contributor');
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

### setPermanentLoginTime
```php
setPermanentLoginTime(int $sec): void
```
**setPermanentLoginTime()** sets the time of permanent login in seconds. The default value is 30 days. If you need unlimited permanent login, set **sec** to 0. Keep on mind that the permanent login is valid until valid permanent login information exists on the users' computer and the server.  
```php
// Set permanent login for 30 days
$login->setPermanentLoginTime(30 * 24 * 60 * 60);
```

### setPermanentLoginStorage
>**Note:** To start using the permanent login, it's required to set permanent login storage. 
```php
setPermanentLoginStorage(callable $factory): void
```
**setPermanentLoginStorage()** sets the factory of permanent login information **Storage**. Permanent login saves the permanent login information to the user's computer and to the server. The Storage is here to solve the server part and to make it flexible. Out of the box, the Login class comes with the file storage, it saves login information to the disk at the server. However, you can write your own Storage, for example, to save login information to the database.
Example of using the file storage:
```php
$login->setPermanentLoginStorage(function () {
    $fs = new \Webiik\Login\Storage\FileStorage();
    $fs->setPath(__DIR__ . '/tmp/permanent');
    return $fs;
});
```

**Write Custom Storage**

You can write your custom storage. Only thing you have to do is to implement `Webiik\Login\Storage\StorageInterface`.

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

User Roles and Action
---------------------
### setAllowedAuthority
```php
setAllowedAuthority(string $role, array $actions = []): void
```
**setAllowedAuthority()** adds allowed user roles and actions.
```php
$login->setAllowedAuthority('contributor', ['admin-read']);
```

### isAuthorized
```php
isAuthorized(string $role, array $actions = []): bool
```
**isAuthorized()** checks if the user is logged in. Then it checks if the role stored in session matches **role**. At least it checks if **role** and **actions** match the [allowed authorities](#setallowedauthority).
```php
if ($login->isAuthorized('contributor', ['admin-read'])) {
    echo 'The user ' . $login->getUserRole() . ' id ' . $login->getUserId() . ' is logged and authorized.';
} else {
    echo 'The user is not logged or authorized.';
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

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues
[3]: https://github.com/webiik/webiik/blob/master/src/Webiik/Session/README.md