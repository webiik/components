Container Component
===================
The Container component adds handy methods to most common Pimple functions and then adds method for automatic injection of dependencies from container.

Resources
---------
* [Documentation][1]
* [Report issues][2]

[1]: https://www.webiik.com
[2]: https://github.com/webiik/webiik/issues

Quick examples
--------------
**Add service to container**
```php
addService(string $name, callable $callable): void
```
```php
$container->addService('\Webiik\Log\Log', function () use (&$container) {
    return new \Webiik\Log\Log($container);   
});
```

**Get service from container**
```php
get(string $name)
```
```php
$log = $container->get('\Webiik\Log\Log');
```

**Inject dependencies from container to your class**
```php
injectTo(string $className): array
```
```php
$myClass = new \MyNameSpace\MyClass(...$container->injectTo('\MyNameSpace\MyClass'));
```
```php
namespace MyNameSpace;

use Webiik\Log\Log;

class MyClass
{
    private $log;
   
    public function __construct(Log $log)
    {
        $this->log = $log;       
    }
}
```
> You can also inject dependencies by service name instead of class name. However it requires three things.
> * Name your service to match the following regex `/ws[A-Z]/`
> ```php
> $container->addService('wsLog', function () use (&$container) {
>     return new \Webiik\Log\Log($container);   
> });
> ```
> * Add class name alias that matches your service name and add doc block with parameter type to __construct
> ```php
> namespace MyNameSpace;
> 
> use Webiik\Log\Log as wsLog;
> 
> class MyClass
> {
>     private $log;
>    
>    /**
>     * @param wsLog $log
>     */
>     public function __construct(wsLog $log)
>     {
>         $this->log = $log;       
>     }
> }
> ```  