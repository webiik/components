<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

Account
=======
The Account provides common interface for user authentication.

Installation
------------
```bash
composer require webiik/account
```

Example
-------
> The following example expects you have already written [your account implementation](#writing-your-custom-account) and you use email and password for user authentication.  
```php
$account = new \Webiik\Account\Account();

// Add your account implementation
$account->addAccount('YourAccount', function () {
	return new \Webiik\Service\YourAccount());
});

// Use one of your account implementations
$account->useAccount('YourAccount');

// Try authenticate a user
try {
    $user = $this->account->auth([
        'email' => 'kitty@webiik.com',
        'password' => 'meow!'
    ]);
    echo 'User ID: ' . $user->getId() . '<br/>';
    echo 'User status: ' . $user->getStatus() . '<br/>';
    echo 'User role: ' . $user->getRole() . '<br/>';
    
    if ($user->hasRole('admin')) {
        // Do something...
    }

} catch (AccountException $e) {
    echo $e->getMessage() . '<br/>';
    print_r($e->getValidationResult());
}
``` 

Settings
--------
### addAccount
```php
addAccount(string $name, callable $accountFactory): void
```
**addAccount()** adds [an implementation](#writing-your-custom-account) of account.
```php
$account->addAccount('YourAccount', function () {
	return new \Webiik\Service\YourAccount());
});
```

### useAccount
```php
useAccount(string $name): void
```
**useAccount()** sets account to be used when calling custom account related methods.
```php
$account->useAccount('YourAccount');
```

### setNamespace
```php
setNamespace(string $namespace): void
```
**setNamespace()** sets authentication namespace. It allows you to use separate authentication for different parts of your application. If you don't set any namespace, it means user belongs to default namespace.  
```php
// Set namespace to 'en'
$account->setNamespace('en');

// Let's say, user 'kitty@webiik.com' is authenticated in
// namespace 'es', so the following authentication fails.
try {
    $user = $this->account->auth([
        'email' => 'kitty@webiik.com',
        'password' => 'meow!'
    ]);   
} catch (AccountException $e) {
    echo $e->getMessage(); // e.g. Account does not exist.
}
```

Writing Your Custom Account 
---------------------------
To write your account implementation you have to extend from abstract class [AccountBase](AccountBase.php) and write your own implementation of methods **auth, signup, update, disable, delete, createToken, activate** and **resetPassword**. Don't forget to incorporate authentication [namespace](#setnamespace) to each method. Read below intended function of each mentioned method and adapt your implementation to it.
 
### auth
```php
auth(array $credentials): User
```
**auth()** authenticates a user with **credentials**. On success returns [User](#user), on error throws [AccountException](#account-exception). Possible exception status codes: METHOD_IS_NOT_IMPLEMENTED, INVALID_CREDENTIAL, INVALID_PASSWORD, ACCOUNT_DOES_NOT_EXIST, ACCOUNT_IS_NOT_ACTIVATED, ACCOUNT_IS_BANNED, ACCOUNT_IS_DISABLED, FAILURE 
```php
try {
    $user = $this->account->auth([
        'email' => 'kitty@webiik.com',
        'password' => 'meow!'
    ]);   
} catch (AccountException $e) {
    echo $e->getMessage();
}
```
> To compare passwords, always use secure method `verifyPassword(string $password, string $hash): bool` provided by [AccountBase](#writing-your-custom-account).

### signup
```php
signup(array $credentials): User
```
**signup()** signs up a user with **credentials**. On success returns [User](#user), on error throws [AccountException](#account-exception). Possible exception status codes: METHOD_IS_NOT_IMPLEMENTED, INVALID_CREDENTIAL, ACCOUNT_ALREADY_EXISTS, FAILURE
```php
try {
    $user = $this->account->signup([
        'email' => 'kitty@webiik.com',
        'password' => 'meow!'
    ]);   
} catch (AccountException $e) {
    echo $e->getMessage();
}
```
> Never store passwords in plain text. [AccountBase](#writing-your-custom-account) provides you secure method to hash passwords `hashPassword(string $password): string`.

### update
```php
update(int $uid, array $data): User
```
**update()** updates **data** on account with id **uid**. On success returns [User](#user), on error throws [AccountException](#account-exception). Possible exception status codes: METHOD_IS_NOT_IMPLEMENTED, ACCOUNT_DOES_NOT_EXIST, INVALID_KEY, FAILURE
```php
try {
    $user = $this->account->update(1, [
    	'email' => 'nyan@webiik.com',
    ]);   
} catch (AccountException $e) {
    echo $e->getMessage();
}
```

### disable
```php
disable(int $uid, int $reason, array $data = []): User
```
**disable()** sets account status to ACCOUNT_IS_DISABLED or ACCOUNT_IS_BANNED on account with id **uid**. On success returns [User](#user), on error throws [AccountException](#account-exception). Possible exception status codes: METHOD_IS_NOT_IMPLEMENTED, ACCOUNT_DOES_NOT_EXIST, FAILURE
```php
try {
    $user = $this->account->disable(1, \Webiik\Account\AccountBase::ACCOUNT_IS_BANNED);   
} catch (AccountException $e) {
    echo $e->getMessage();
}
```

### delete
```php
delete(int $uid, array $data = []): User
```
**delete()** deletes an account with id **uid**. On success returns [User](#user), on error throws [AccountException](#account-exception). Possible exception status codes: METHOD_IS_NOT_IMPLEMENTED, ACCOUNT_DOES_NOT_EXIST, FAILURE
```php
try {
    $user = $this->account->delete([
        'uid' => 1,       
    ]);   
} catch (AccountException $e) {
    echo $e->getMessage();
}
```

### createToken
```php
createToken(): string
```
**createToken()** creates and returns time limited security token. On error throws [AccountException](#account-exception). Possible exception status codes: METHOD_IS_NOT_IMPLEMENTED, FAILURE
```php
try {
    $token = $this->account->createToken();
    
    // Send token to user, so he can perform an action...
     
} catch (AccountException $e) {
    echo $e->getMessage();
}
```

### activate
```php
activate(string $token): User
```
**activate()** activates an account by valid **token**. On success returns [User](#user), on error throws [AccountException](#account-exception). Possible exception status codes: METHOD_IS_NOT_IMPLEMENTED, INVALID_TOKEN, FAILURE
```php
try {
    $user = $this->account->activate($token);
} catch (AccountException $e) {
    echo $e->getMessage();
}
```

### resetPassword
```php
resetPassword(string $token, string $password): User
```
**resetPassword()** updates account **password** by valid **token**. On success returns [User](#user), on error throws [AccountException](#account-exception). Possible exception status codes: METHOD_IS_NOT_IMPLEMENTED, INVALID_TOKEN, FAILURE
```php
try {
    $user = $this->account->resetPassword($token, 'new-password');
} catch (AccountException $e) {
    echo $e->getMessage();
}
```
> Never store passwords in plain text. [AccountBase](#writing-your-custom-account) provides you secure method to hash passwords `hashPassword(string $password): string`.

User
----
User is an object returned by several Account methods. User provides handy methods to work with authenticated user.

### __construct
```php
__construct(int $status, $id, string $role = '', array $info = [])
```
**__construct()** creates User object.

**Parameters**
* **status** user account status. Usual status codes are: ACCOUNT_IS_OK and ACCOUNT_IS_NOT_ACTIVATED
* **id** unique user account id.
* **role** (optional) user role.
* **info** (optional) additional user account info.
```php
$user = new User(self::ACCOUNT_IS_OK, 1, 'user');
```

### getId
```php
getId()
```
**getId()** returns unique user account id. 
```php
$uid = $user->getId();
```

### getRole
```php
getRole(): string
```
**getRole()** returns user role.
```php
$role = $user->getRole();
```

### hasRole
```php
hasRole(string $role): bool
```
**hasRole()** checks if user has given **role**.
```php
if ($user->hasRole('user') {
    // Do something...
}
```

### getInfo
```php
getInfo(): array
```
**getInfo()** returns additional user account info.
```php
$info = $user->getInfo();
```

### needsActivation
```php
needsActivation(): bool
```
**needsActivation()** checks if user account requires activation.
```php
if ($user->needsActivation()) {
    // Send activation to user email
}
```

Account Exception
-----------------
AccountException has to be thrown by all implemented account methods. Unlike standard Exception, AccountException has one extra parameter **validationResult**. This parameter is intended to store invalid credentials in the following format e.g. ['email' => ['Invalid email'], 'password' => ['Password is too short.']]

### __construct
```php
__construct($message = '', $code = 0, \Throwable $previous = null, array $validationResult = [])
```
**__construct()** creates AccountException object.
```php
throw new AccountException(self::MSG_INVALID_CREDENTIAL, self::INVALID_CREDENTIAL, null, ['email' => ['Invalid email'], 'password' => ['Password is too short.']]);
```

### getValidationResult
```php
getValidationResult(): array
```
**getValidationResult()** returns invalid credentials. 
```php
try {
    $user = $this->account->auth([
        'email' => 'kitty@webiik.com',
        'password' => 'meow!'
    ]);   
} catch (AccountException $e) {
    print_r($e->getValidationResult()); // e.g. ['email' => ['Invalid email'], 'password' => ['Password is too short.']]
}
```

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues