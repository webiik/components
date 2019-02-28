<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-2-brightgreen.svg"/>
</p>

Middleware
==========
The Middleware is middleware launcher that allows automatic constructor DI and sending custom data among middleware. 

Installation
------------
```bash
composer require webiik/middleware
```

Example
-------
```php
$middleware = new \Webiik\Middleware\Middleware($container, $data);
$middleware->add('MwTest:run', ['foo' => 'bar']);
$middleware->run();
```
Writing Middleware
------------------
Every middleware has to be a class with at least one public method with the following parameters **callable $next, [\Webiik\Data\Data $data][4]**. Every class can contain more methods with the mentioned parameters.
```php
class MwTest
{
    public function run(callable $next, \Webiik\Data\Data $data)
    {
        // Get middleware data
        $mwData = $data->getAll(); // Array([foo] => [bar])
         
        // Change middleware data 
        $data->set('foo', 'foo');
        
        // Launch next middleware
        $next();
        
        // Continue current middlware...
    }
}
```
You can also use **constructor DI** to inject services from container to middleware. [Read more about DI and container][3]. 

Adding
------
### add
```php
add(string $controller, $data = []): void
```
**add()** ads middleware to queue.

**Parameters:** 
* **controller** string representation of controller and method to be initiated e.g. controllerName:methodName
* **data** a key-value array of data to be injected during the middleware initiation 
```php
$middleware->add('MwTest:run');
```

Launch
------
### run
```php
run(): void
```
**run()** runs all middleware in queue.
```php
$middleware->run();
```

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues
[3]: ../Container/README.md
[4]: ../Data/README.md

