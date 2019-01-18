<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-3-brightgreen.svg"/>
</p>

Login
=====
The Login provides methods to manage user login state. It supports:
* permanent login
* automatic logout of inactive users
* user roles and actions
* login resolution (e.g. by lang, app part, ...)

> Note: This class is meant to be used after the successful user authentication and authorization.

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
* [Basic Login][5]
* [Permanent Login][6]
* [Login Check][7]
* [User Roles and Action][8]
* [Login Resolution][9]
* [Logout][10]

Basic Login
-----------
#### Login
```php
login($uid, bool $permanent = false, string $role = ''): void
``` 
To log in the user use:
```php
$login->login(1);
```
Login is valid until the user closes the browser or the [PHP session][3] expires.

#### Session Name
Login state is stored in PHP session. You can change the name of session where the login state is stored: 
```php
setSessionName(string $sessionName): void
``` 
```php
$login->setSessionName('myLoginSessionName');
```

Permanent Login
---------------
#### Storage
**To use the permanent login you have to set the storage at first.** Permanent login saves the permanent login information to the user's computer and to the server. The storage is here to solve the server part and to make it flexible. Out of the box, the Login class comes with the file storage, it saves login information to the disk at the server. However, you can write your own storage, for example, to save login information to the database. 
```php
setPermanentLoginStorage(callable $factory): void
```
Example of using the file storage:
```php
$login->setPermanentLoginStorage(function () {
    $fs = new \Webiik\Login\Storage\FileStorage();
    $fs->setPath(__DIR__ . '/tmp/permanent');
    return $fs;
});
```

#### Writing Custom Storage
Every custom storage has to implement `Webiik\Login\Storage\StorageInterface`.

#### Login
```php
login($uid, bool $permanent = false, string $role = ''): void
``` 
To log in the user permanently use:
```php
$login->login(1, true);
```
> To use permanent login, don't forget to set [storage][4].

The permanent login is valid until valid permanent login information exists on the user's computer and the server. You can set the exact time of the permanent login:
```php
setPermanentLoginTime(int $sec): void
```
```php
// Set permanent login for 30 days
$login->setPermanentLoginTime(30 * 24 * 60 * 60);
```  

#### Cookie Name
You can set the name of cookie where the permanent login information is stored at the user's computer:
```php
setPermanentCookieName(string $name): void
```
```php
$login->setPermanentCookieName('myPermanentCookie');
```

Login Check
-----------
```php
isLogged(): bool
```
```php
if ($login->isLogged()) {
    echo 'The user id ' . $login->getUserId() . ' is logged.';
} else {
    echo 'The user is not logged.';
}
```

User Roles and Action
---------------------
#### Add Allowed Roles and Actions
To start using user roles and actions you have to add allowed authorities:
```php
setAllowedAuthority(string $role, array $actions = []): void
```
```php
$login->setAllowedAuthority('contributor', ['admin-read']);
```
> Note: Actions are optional.

#### Login with Role
```php
login($uid, bool $permanent = false, string $role = ''): void
``` 
To log in the user with the specific role use:
```php
$login->login(1, 'contributor');
```

#### User Role and Actions Check
Check if the user is logged in and if he/she has access privileges:
```php
isAuthorized(string $role, array $actions = []): bool
``` 
```php
if ($login->isAuthorized('contributor', ['admin-read'])) {
    echo 'The user ' . $login->getUserRole() . ' id ' . $login->getUserId() . ' is logged and authorized.';
} else {
    echo 'The user is not authorized or even logged.';
}
```

Login Resolution
----------------
If you want to make the login valid only for the specific part of your app, use the login resolution. Imagine you have a multilingual app and you want to make a separate account for every language - this is the situation when the login resolution can help.
```php
setLoginSection(string $name): void
```
```php
// Always set this right after instantiation the Login class 
$login->setLoginSection('en');
```

Logout
------
```php
logout(): void
```
```php
$login->logout();
```

#### Automatic Logout
> Automatic logout of inactive users is available only for basic login.

To activate automatic logout of inactive users, set the time of automatic logout:
```php
setAutoLogoutTime(int $sec): void
```
```php
// Set the automatic logout after 5 minutes of inactivity
// Always set this right after instantiation the Login class
$login->setAutoLogoutTime(5 * 60);
```
**Don't forget** to update the time of last user activity with every http request: 
```php
updateAutoLogoutTs(): void
```
```php
// Never call this before isLogged or isAuthorized
// It would let to the situation, that the user was never logged out
$login->updateAutoLogoutTs();
``` 

Resources
---------
* [Webiik framework][1]
* [Report issues][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/webiik-components/issues
[3]: https://github.com/webiik/webiik/blob/master/src/Webiik/Session/README.md
[4]: #storage
[5]: #basic-login
[6]: #permanent-login
[7]: #login-check
[8]: #user-roles-and-action
[9]: #login-resolution
[10]: #logout
