<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

Validator
=========
The Validator validates data against user-defined rules.

Installation
------------
```bash
composer require webiik/validator
```

Example
-------
```php
$validator = new \Webiik\Validator\Validator();

// Add input to validate and input validation rules
$validator->addInput('meow', function () {
    return [
        new \Webiik\Validator\Rules\StrLenMin(5, 'Err: Input is shorter than 5 chars.'),
        new \Webiik\Validator\Rules\StrLenMax(10, 'Err: Input is longer than 10 chars.'),
    ];
}, 'greeting');

// Validate all inputs and eventually get array of all invalid inputs
$invalid = $validator->validate(); // Array ( [greeting] => Array ( [0] => Err: Input is shorter than 5 chars. ) ) 
```

Adding Inputs For Validation
----------------------------
### addInput
```php
addInput($input, callable $rules, string $name = ''): void
```
**addInput()** adds an input for validation.

**Parameters**
* **input** input value
* **rules** callable that return an array of validation rule objects
* **name** optional input name. This name is used as input index in validate() result.  
```php
$validator->addInput('meow', function () {
    return [
        new \Webiik\Validator\Rules\StrLenMin(5, 'Err: Input is shorter than 5 chars.'),
        new \Webiik\Validator\Rules\StrLenMax(10, 'Err: Input is longer than 10 chars.'),
    ];
}, 'greeting');
```

Validation Rules
----------------
#### Write Custom Validation Rule

You can write your custom validation rule. The only thing you have to do is to implement [RuleInterface](Rules/RuleInterface.php). Look at existing [implementation of RuleInterface](Rules/Equal.php) to get better insight.

#### Available Validation Rules
```php
// Check if input is === $val
Equal($val, string $errMsg = '')
```
```php
// Check if input is >= $min and <= $max
IntVal(int $min, int $max, string $errMsg = '')
```
```php
// Check if input is <= $max
IntValMax(int $max, string $errMsg = '')
```
```php
// Check if input is >= $min
IntValMin(int $max, string $errMsg = '')
```
```php
// Check if input is email address
isEmail(string $errMsg = '')
```
```php
// Check if input is_float()
isFloat(string $errMsg = '')
```
```php
// Check if input is_int()
isInt(string $errMsg = '')
```
```php
// Check if input is_numeric()
isNumeric(string $errMsg = '')
```
```php
// Check if input is_object()
isObject(string $errMsg = '')
```
```php
// Check if input is not empty
isRequired(string $errMsg = '')
```
```php
// Check if input is_string()
isString(string $errMsg = '')
```
```php
// Check if input passes FILTER_VALIDATE_URL
isUrl(string $errMsg = '')
```
```php
// Check if input matches $regex
regex(string $regex, string $errMsg = '')
```
```php
// Check if input length is >= $min and <= $max
StrLen(int $min, int $max, string $errMsg = '')
```
```php
// Check if input length is >= $min
StrLenMin(int $min, string $errMsg = '')
```
```php
// Check if input length is <= $max
StrLenMax(int $max, string $errMsg = '')
```

Validating Inputs
-----------------
### validate
```php
validate($testAllRules = false): array
```
**validate()** validates all inputs and returns array of all invalid inputs sorted by input index.

**Parameters**
* **testAllRules** indicates if unfulfilled rule stops next rules checking
```php
$invalid = $validator->validate();
```

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues