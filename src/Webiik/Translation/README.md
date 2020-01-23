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

### inject
```php
inject(string $parserClassName, TranslationInjector $injector): void
```
**inject()** injects dependencies to specific parser. It is useful when you make your custom parser and you need to inject some external dependencies.  
```php
$translation->inject('Route', new \Webiik\Translation\TranslationInjector(function () use (&$router) {
    return [$router];
}));
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
get(string $key, array|bool|null $parse = null): string|array
```
**get()** gets translation by key. Key supports dot notation. If key is missing it returns empty string. After calling, all missing keys and contexts can be obtained with method **getMissing()**.
```php
$translation->get('greeting', ['name' => 'Kitty']);
```

### getAll
```php
getAll(array|bool|null $parse = null): array
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

Parsing
-------
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
{variableName, Plural, {condition message}...}
```
Sometimes a translation depends on some specific count. **Int** represents that count, allowed values are: **int, int-int, int-, int+** 
```php
$translation->add('cats', '{numCats, Plural, {0- No cats.} {1 One cat.} {2+ {numCats} cats.}}');
echo $translation->get('cats', ['numCats' => 2]);
// 2 cats.
```

#### Select Syntax
```
{variableName, Select, {condition message}...}
```
Sometimes a translation depends on some specific value. In the select syntax, **string** represents that value. 
```php
$translation->add('hello-cat', '{gender, Select, {male Hello Tom!} {female Hello Kitty!}}');
echo $translation->get('hello-cat', ['gender' => 'male']);
// Hello Tom!
```

#### Link Syntax
```
{Link, {link text} {url} {target} {rel}}
```
Sometimes a translation depends on some specific value. In the select syntax, **string** represents that value. 
```php
$translation->add('link', 'Visit the {Link, {official page} {https://www.webiik.com} {_blank} {nofollow}}.');
echo $translation->get('link', true);
// Visit the <a href="https://www.webiik.com" target="_blank" rel="nofollow">official website</a>.
```
> ⚠️ Notice the true parameter when calling the method `get`. Without it, the link would be not generated, and the text of translation would be displayed in the original format. 

#### Custom Parser Syntax
```
{variableName, FormatterClassName, formatter syntax}
```
or
```
{FormatterClassName, formatter syntax}
```
You can write your own parser. Every custom parser must: 
* be compatible with the syntax above
* implement **Webiik\Translation\Parser\ParserInterface.php**
* use namespace **Webiik\Translation\Parser**

Look at [Select](Parser/Select.php) parser to get better insight.  

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues