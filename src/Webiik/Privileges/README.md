<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

Privileges
==========
The Privileges class manages user authorization.

Installation
------------
```bash
composer require webiik/privileges
```

Example
-------
```php
$privileges = new \Webiik\Privileges\Privileges();

// Add roles
$privileges->addRole('user');
$privileges->addRole('admin');

// Add resources
$privileges->addResource('article', ['create', 'read', 'update', 'delete']);

// Allow access to resources
$privileges->allow('user', 'article', ['read']);
$privileges->allow('admin', 'article', ['all']);

// Test access to resources
if ($privileges->isAllowed('admin', 'article', 'update')) {
    // Admin can update an article
}
```

Adding Roles and Resources
--------------------------
### addRole
```php
addRole(string $role): void
```
**addRole()** adds user **role**.
```php
$privileges->addRole('user');
```

### addResource
```php
addResource(string $resource, array $privileges): void
```
**addResource()** adds **resource** and supported resource **privileges**. Never set privilege 'all', resource will be not added.
```php
$privileges->addResource('article', ['create', 'read', 'update', 'delete']);
```

Allowing Access to Resources
----------------------------
### allow
```php
allow(string $role, string $resource, array $privileges): void
```
**allow()** allows **role** access to the **resource** with given **privileges**. If role, resource or one of privileges doesn't exist, rule will be not added. If you want to grant all privileges, set **privileges** to **['all']**.
```php
$privileges->allow('user', 'article', ['read']);
```

Checking Access to Resources
----------------------------
### isAllowed
```php
isAllowed(string $role, string $resource, string $privilege): bool
```
**isAllowed()** checks if user with **role** can do **privilege** on **resource**. 
```php
if ($privileges->isAllowed('user', 'article', 'read')) {
    // User can read an article
}
```

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues