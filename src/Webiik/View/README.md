<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

View
====
The View provides common interface for rendering templates, no matter what template engine you want to use. Out of the box it supports Twig template engine.

Installation
------------
```bash
composer require webiik/view
```

Example
-------
```php
$view = new \Webiik\View\View();

// Prepare Twig renderer factory
$renderer = function() {
    // Instantiate Twig template engine
    $loader = new Twig_Loader_Filesystem(__DIR__ . '/view');
    $environment = new Twig_Environment($loader, array(
        'cache' => __DIR__ . '/tmp/view'
    ));
    
    // Instantiate and return Twig renderer 
    return new \Webiik\View\Renderer\Twig($environment);
};

// Add renderer
$view->setRenderer($renderer);

// Render template
echo $view->render('test.twig', ['foo' => 'meow']);
```

Configuration
-------------
### setRenderer
```php
setRenderer(callable $factory):void
```
**setRenderer()** adds a renderer - a factory of implementation of RendererInterface on template engine. 
```php
$view->setRenderer($renderer);
```

**Write Custom Renderer**

You can write your custom renderer. The only thing you have to do is to implement [RendererInterface](Renderer/RendererInterface.php). Look at the [implementation of RendererInterface on Twig](Renderer/Twig.php) template engine and get better insight.

Rendering
---------
### render
```php
render(string $template, array $data = []): string
```
**render()** renders template to string.

**Parameters**
* **template** name of template to render
* **data** array of data to pass to template 
```php
echo $view->render('test.twig', ['foo' => 'meow']);
```

Other
-----
### getTemplateEngine
```php
getTemplateEngine()
```
**getTemplateEngine()** gets object of template engine using by renderer.
```php
$view->getTemplateEngine(); // e.g. returns Twig_Environment
```

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues