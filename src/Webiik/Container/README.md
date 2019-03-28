<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-1-brightgreen.svg"/>
</p>

Container
=========
The Container adds handy methods to most common Pimple functions and then adds automatic injection of dependencies from container to class constructor.

Installation
------------
```bash
composer require webiik/container
```

Example
-------
```php
$container = new \Webiik\Container\Container();
$container->addService('\Webiik\Arr\Arr', function () {
    return new \Webiik\Arr\Arr();
});
$array = $container->get('\Webiik\Arr\Arr');
```

Adding
------
### addService
```php
addService(string $name, callable $factory): void
```
**addService()** ads service factory to container. It returns always same instance of service. 
```php
$container->addService('\Webiik\Arr\Arr', function () {
    return new \Webiik\Arr\Arr();
});
```
If you need to access container inside **$factory**:
```php
$container->addService('Service', function ($container) {
    // $container - Container    
});
```

### addServiceFactory
```php
addServiceFactory(string $name, callable $factory): void
```
**addServiceFactory()** ads service factory to container. It returns always new instance of service.
```php
$container->addService('\Webiik\Arr\Arr', function () {
    return new \Webiik\Arr\Arr();
});
```

### addParam
```php
addParam(string $name, $val): void
```
**addParam()** ads parameter to container.
```php
$container->addParam('foo', 'bar');
```

### addFunction
```php
addFunction(string $name, callable $function): void
```
**addFunction()** ads function to container.
```php
$container->addFunction('myFn', function ($a, $b) {
    return $a * $b;
});
```

Check
-----
### isIn 
```php
isIn(string $name): bool
```
**isIn()** checks if service, parameter or function is stored in container.
```php
$container->isIn('\Webiik\Arr\Arr');
```

Getting
-------
### get
```php
get(string $name)
```
**get()** returns service, parameter or function from container.
```php
$array = $container->get('\Webiik\Arr\Arr');
```

Dependency Injection
--------------------
Container provides automatic dependency injection from Container to class controller using the method `injectTo(string $className): array`. However it requires to follow these naming conventions:
 
### Inject Service by Class Name
1. Add service with same name as full name of underlying class:
   ```php
   $container->addService('\Webiik\Arr\Arr', function () {
      return new \Webiik\Arr\Arr();   
   });
   ```
2. Use full class name as type parameter in controller in your class:
   ```php   
   public function __construct(\Webiik\Arr\Arr $array)
   {
       $this->array = $array;
   }
   ```
   > Container will search for service with name `\Webiik\Arr\Arr`. 
3. Inject dependencies to class:
   ```php
   $myClass = new MyClass(...$container->injectTo('MyClass'));
   ```

### Inject Service by Service Name
1. Add service with name matching the following regex `ws[A-Z]`:
   ```php
   $container->addService('wsArray', function () {
      return new \Webiik\Arr\Arr();   
   });
   ```
2. Add class name alias to your class:
   ```php
   use Webiik\Arr\Arr as wsArray;
   ```
3. Add doc block with parameter type to controller of your class:   
   ```php
   /**
   * @param wsArray $array
   */   
   public function __construct(wsArray $array)
   {
       $this->array = $array;
   }
   ```
   > Container will search for service with name `wsArray`. 
4. Inject dependencies from container to your class:
   ```php
   $myClass = new MyClass(...$container->injectTo('MyClass'));

### Inject Function or Parameter
1. Add parameter with any name:
   ```php
   $container->addFunction('myFnName', function() {
       echo 'Hello!';
   });
   ```
2. Use parameter name in constructor in your class:
   ```php   
   public function __construct($myFnName)
   {
       $myFnName(); // Hello
   }
   ```
   > Container will search for parameter with name `myParamName`. 
3. Inject dependencies from container to your class:
   ```php
   $myClass = new MyClass(...$container->injectTo('MyClass'));
   ```
   
### Inject Container Itself
1. Use full Container class name as type parameter in constructor in your class:
   ```php   
   public function __construct(\Webiik\Container\Container $container)
   {
       $this->container = $container;
   }
   ``` 
2. Inject dependencies from container to your class:
   ```php
   $myClass = new MyClass(...$container->injectTo('MyClass'));
   ```

Resources
---------
* [Webiik framework][1]
* [Report issue][2]
* [Pimple][3]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues
[3]: https://github.com/silexphp/Pimple  