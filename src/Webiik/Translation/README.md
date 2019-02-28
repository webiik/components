<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-1-brightgreen.svg"/>
</p>

Translation
===========
The translation provides i18n with user-extensible translation formatting.

Installation
------------
```bash
composer require webiik/translation
```

Example
-------
```php
$translation = new \Webiik\Translation\Translation($arr);

$translation->setLang('en');
$translation->add('greeting', 'Hello {name}!');
echo $translation->get('greeting', ['name' => 'Kitty']);

$translation->setLang('cs');
$translation->add('greeting', 'Ahoj {name}!');
echo $translation->get('greeting', ['name' => 'Kitty']);
```

Setting
-------
### setLang
```php
setLang(string $lang): void
```
**setLang()** sets current lang of translation. This lang is used for setting and getting values to/from Translation class. 
```php
$translation->setLang('en');
```

Adding
------
### add
```php
add(string $key, string $val): void
```
**add()** adds translation by key. Read more about [supported translation formats](#translation-formatting).
```php
$translation->add('greeting', 'Hello {name}!');
```

### addArr
```php
addArr(array $translation, &$context = false): void
```
**addArr()** adds translations from array.

Note about resolving the key conflicts:
                                          
**Arrays values** - New value that is an array is merged with old value that is an array.
If array key is a string, value of the new key replaces value of the old key.

**Mixed values** - New value that is a different type than old value, replaces old value.
e.g. New string value replaces old array value and vice-versa.
```php
$translation->addArr(['greeting' => 'Hello {name}!']);
```

Getting
-------
### get
```php
get(string $key, $context = null)
```
**get()** gets translation by key. Key supports dot notation. If key is missing it returns empty string. After calling, all missing keys and contexts can be obtained with method **getMissing()**.
```php
$translation->get('greeting', ['name' => 'Kitty']);
```

### getAll
```php
getAll($context = null): array
```
**getAll()** gets all translations. After calling, all missing contexts can be obtained with method **getMissing()**.
```php
$translation->getAll(['name' => 'Kitty']);
```

### getMissing
```php
getMissing(): array
```
**getMissing()** returns array of all missing keys and contexts from callings of methods get() and getAll().
```php
$missing = $translation->$arr->getMissing();
```

Translation Formatting
----------------------
Translations can contain special formatting which help to update translation values on the fly.

#### Basic Syntax
```
{var}
```
You can add any variable to translation with folded brackets.
```php
$translation->add('greeting', 'Hello {name}!');
echo $translation->get('greeting', ['name' => 'Kitty']);
// Hello Kitty!
```

#### Plural Syntax
```
{variableName, Plural, =int {message}...}
```
Sometimes a translation depends on some specific count. **Int** represents that count, allowed values are: **-int, int-int, int+** 
```php
$translation->add('playful-cats', '{numCats, Plural, =0 {No cat wants} =1 {One cat wants} =2-10 {{numCats} cats want} =11+ {A lot of cats want}} to play.');
echo $translation->get('playful-cats', ['numCats' => 2]);
// 2 cats want to play.
```

#### Select Syntax
```
{variableName, Select, =string {message}...}
```
Sometimes a translation depends on some specific value. In the select syntax, **string** represents that value. 
```php
$translation->add('cat-gender', '{gender, Select, =tomcat {He is {gender}} =cat {She is {gender}}}.');
echo $translation->get('cat-gender', ['gender' => 'tomcat']);
// He is tomcat.
```

#### Custom Formatter Syntax
```
{variableName, FormatterClassName, formatter syntax}
```
You can write your own formatter. Every custom formatter must: 
* be compatible with the syntax above
* implement **Webiik\Translation\Parser\ParserInterface.php**
* use namespace **Webiik\Translation\Parser**

Look at [Select](Parser/Select.php) formatter to get better insight.  

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues