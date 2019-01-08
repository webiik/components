<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-1-brightgreen.svg"/>
</p>

Container
=========
The Container adds handy methods to most common Pimple functions and then adds automatic injection of dependencies from container to class controller.

Example
-------
```php
$container = new \Webiik\Container\Container();
$container->addService('\Webiik\Array\Array', function () {
    return new \Webiik\Array\Array();
});
$array = $container->get('\Webiik\Array\Array');
```

Adding
------
#### Add Service
Always same instance of service will be returned.
```php
addService(string $name, callable $factory): void
```
> `$factory` must always return object
```php
$container->addService('\Webiik\Array\Array', function () {
    return new \Webiik\Array\Array();
});
```
#### Add Service Factory
Always new instance of service will be returned.
```php
addServiceFactory(string $name, callable $factory): void
```
> `$factory` must always return object
#### Add Parameter
```php
addParam(string $name, $val): void
```
#### Add Function
```php
addFunction(string $name, callable $function): void
```
```php
$container->addFunction('myFn', function ($a, $b) {
    return $a * $b;
});
```
#### Access Container Within Callable
```php
$container->addService('Service', function ($pimple) use ($container) {
    // $container - Container
    // $pimple - Pimple    
});
```

Getting
-------
#### Get
Get service, parameter or function from container.
```php
get(string $name)
```
```php
$array = $container->get('\Webiik\Array\Array');
```
#### Check
Check if service, parameter or function is stored in container. 
```php
isIn(string $name): bool
```
```php
$container->isIn('\Webiik\Array\Array');
```

Dependency Injection
--------------------
Container provides automatic dependency injection from Container to class controller using the method `injectTo(string $className): array`. However it requires to follow these naming conventions:
 
#### Inject Service by Class Name
1. Add service with same name as full name of underlying class:
   ```php
   $container->addService('\Webiik\Array\Array', function () {
      return new \Webiik\Array\Array();   
   });
   ```
2. Use full class name as type parameter in controller in your class:
   ```php   
   public function __construct(\Webiik\Array\Array $array)
   {
       $this->array = $array;
   }
   ```
   > Container will search for service with name `\Webiik\Array\Array`. 
3. Inject dependencies:
   ```php
   $myClass = new MyClass(...$container->injectTo('MyClass'));
   ```

#### Inject Service by Service Name
1. Add service with name matching the following regex `/ws[A-Z]/`:
   ```php
   $container->addService('wsArray', function () {
      return new \Webiik\Array\Array();   
   });
   ```
2. Add class name alias to your class:
   ```php
   use Webiik\Array\Array as wsArray;
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
4. Inject dependencies:
   ```php
   $myClass = new MyClass(...$container->injectTo('MyClass'));

#### Inject Function
1. Add parameter with any name:
   ```php
   $container->addFunction('myFnName', function() {
       echo 'Hello!';
   });
   ```
2. Use parameter name in controller in your class:
   ```php   
   public function __construct($myFnName)
   {
       $myFnName(); // Hello
   }
   ```
   > Container will search for parameter with name `myParamName`. 
3. Inject dependencies:
   ```php
   $myClass = new MyClass(...$container->injectTo('MyClass'));
   ```
   
#### Inject Parameter
1. Add parameter with any name:
   ```php
   $container->addParam('myParamName', 'Hello!');
   ```
2. Use parameter name in controller in your class:
   ```php   
   public function __construct($myParamName)
   {
       echo $myParamName; // Hello
   }
   ```
   > Container will search for parameter with name `myParamName`. 
3. Inject dependencies:
   ```php
   $myClass = new MyClass(...$container->injectTo('MyClass'));
   ```   

Resources
---------
* [Webiik framework][1]
* [Report issues][2]
* [Pimple][3]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/webiik-components/issues
[3]: https://github.com/silexphp/Pimple  