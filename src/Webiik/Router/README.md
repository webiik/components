<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

Router
======
The Router is **passive**, multi-lingual regex router. It supports route names, route parameters, route controllers and route middleware. **Passive** means that it doesn't set HTTP headers and it doesn't invoke route controllers and middleware. It just tests a request URI against the defined routes and returns all necessary data to build a route.   

Installation
------------
```bash
composer require webiik/router
```

Example
-------
```php
$router = new \Webiik\Router\Router();

// Set base URI
$router->setBaseURI(dirname($_SERVER['SCRIPT_NAME']));

// Add route(s)
$router->addRoute(['get'], '/', 'Home:run', 'home-page');

// Check if current URI matches some route
$route = $router->match();

if ($router->getHttpCode() == 200) {
    // 200 - OK
    $route->getLang(); // en
    $route->getName(); // home-page
    $route->getController(); // ['Home', 'run']   
} elseif ($router->getHttpCode() == 405) {
    // 405 - Method Not Allowed
} elseif ($router->getHttpCode() == 404) {
    // 404 - Not Found
}
```

Configuration
-------------
### setBaseURI
```php
setBaseURI(string $baseURI): void
```
**setBaseURI()** sets the base directory of your index.php file relatively to web-server root.
```php
$router->setBaseURI(dirname($_SERVER['SCRIPT_NAME']));
```
> Every time your index.php file isn't in the web-server root directory, you have to set dir in which is located.  

### setDefaultLang
```php
setDefaultLang(string $defaultLang): void
```
**setDefaultLang()** sets the default language of routes without defined **$lang** parameter. **$defaultLang** must be two characters long. The default value is **en**.
```php
$router->setDefaultLang('en');
```

### setDefaultLangInURI
```php
setDefaultLangInURI(bool $defaultLangInURI): void
```
**setDefaultLangInURI()** determines if default language is part of URI e.g. /en/. The default value is **FALSE**.
```php
$router->setDefaultLangInURI(true);
```

Adding
------
### addRoute
```php
addRoute(array $methods, string $route, string $controller, string $name = '', string $lang = ''): NewRoute
```
**addRoute()** adds **NewRoute**  to the **Router** and returns **NewRoute**. 

**Parameters:** 
* **methods** array of route http methods
* **route** route URI regex (without delimiters)
* **controller** string representation of controller e.g. controllerName:methodName
* **name** route name
* **lang** two letter route lang prefix, if it's not set, the default lang is used instead 
```php
// Add route
$router->addRoute(['get'], '/', 'Home:run');

// Add route with more http methods
$router->addRoute(['get', 'post'], '/contact', 'Contact:run');

// Add named route
$router->addRoute(['get'], '/', 'Home:run', 'home-page');

// Add named route in specific language 
$router->addRoute(['get'], '/', 'Home:run', 'home-page', 'en');

// Add route with route middleware
$router->addRoute(['get'], '/', 'Home:run')->mw('Class:method');

// Add case sensitive route
$router->addRoute(['get'], '/CaMeL', 'Camel:run')->sensitive();

// To add routes with route parameters use regex groups.
// Every regex group represents one route parameter.

// Add route with required parameter
$router->addRoute(['get'], '/portfolio/(?<client>[a-z0-9]+)', 'Portfolio:run');

// Add route with optional parameter
$router->addRoute(['get'], '/portfolio/(?<client>[a-z0-9]+)?', 'Portfolio:run');
```

Check
-----
### match
```php
match()
```
**match()** checks if current request URI matches some of defined route. If yes it returns **Route**, otherwise **FALSE**.
```php
$route = $router->match();
if ($route) {
    // 200 OK
} else {
    // 404 Not Found or 405 Method Not Allowed
}
```

### getHttpCode
```php
getHttpCode(): int
```
**getHttpCode()** returns http code of the result of last [**match()**][3].
```php
$route = $router->match();
$httpCode = $router->getHttpCode();
if ($httpCode == 200) {
    // 200 OK
} elseif ($httpCode == 405) {
    // 405 Method Not Allowed
} elseif ($httpCode == 404) {
    // 404 Not Found
}
```

Getting
-------
### getBaseURL
```php
getBaseURL(): string
```
**getBaseURL()** returns base URL of your app e.g. https://www.webiik.com
```php
$baseUrl = $router->getBaseURL();
```

Route
=====
The Route is the result of successful [**match()**][3]. It contains all known information about the matched route.  

### getMethods
```php
getMethods(): array
```
**getMethods()** returns route methods. 
```php
$route->getMethods();
```

### getRegex
```php
getRegex(): string
```
**getRegex()** returns route definition.
```php
$route->getRegex();
```

### getController
```php
getController(): array
```
**getController()** returns array with route controller and controller method to run.
```php
$route->getController();
```

### getName
```php
getName(): string
```
**getName()** returns route name.
```php
$route->getName();
```

### getLang
```php
getLang(): string
```
**getLang()** returns route language.
```php
$route->getLang();
```

### getMw
```php
getMw(): array
```
**getMw()** returns array with route middleware.
```php
$route->getMw();
```

### getParameters
```php
getParameters(): array
```
**getParameters()** returns parameters injected during Route construction e.g. ['name' => 'dolly', '1' => 'dolly', '2' => 'hello'].
```php
$route->getParameters();
```

### getRegexParameters
```php
getRegexParameters(string $lang = ''): array
```
**getRegexParameters()** returns route regex parameters e.g. ['0' => '(?\<name\>[a-z]*)?', '1' => '([a-z]*)'].
```php
$route->getRegexParameters();
```

### getURI
```php
getURI(array $parameters = [], string $lang = ''): string
```
**getURI()** returns URI of this route or empty string if it can't get URI. Usually it returns empty string when parameters are missing or route doesn't exist in required language. 
```php
$route->getURI();
```

### getURL
```php
getURL(array $parameters = [], string $lang = ''): string
```
**getURL()** returns URL of this route or empty string if it can't get URL. Usually it returns empty string when parameters are missing or route doesn't exist in required language.
```php
$route->getURL();
```

### getMissingParameters
```php
getMissingParameters(): array
```
**getMissingParameters()** returns missing parameters after calling **getURI()** or **getURL()**.
```php
$route->getMissingParameters();
```

### isSensitive
```php
isSensitive(): bool
```
**isSensitive()** indicates if route definition (regex) is case sensitive.
```php
$route->isSensitive();
```

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues
[3]: #match