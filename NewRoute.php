<?php
declare(strict_types=1);

namespace Webiik\Router;

class NewRoute
{
    /**
     * @var array
     */
    public $httpMethods = [];

    /**
     * @var string
     */
    public $regex = '';

    /**
     * @var string
     */
    public $controller = '';

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $lang = '';

    /**
     * @var array
     */
    public $middleware = [];

    /**
     * @var bool
     */
    public $sensitive = false;

    /**
     * Route constructor.
     * @param array $httpMethods
     * @param string $regex
     * @param string $controller
     * @param string $name
     * @param string $lang
     */
    public function __construct(array $httpMethods, string $regex, string $controller, string $name, string $lang)
    {
        $this->httpMethods = $httpMethods;
        $this->regex = '~^' . $regex . '$~' . ($this->sensitive ? '' : 'i');
        $this->controller = $controller;
        $this->name = $name;
        $this->lang = $lang;
    }

    /**
     * @return NewRoute
     */
    public function sensitive(): NewRoute
    {
        $this->sensitive = true;
        return $this;
    }

    /**
     * Add route middleware in format ClassName:MethodName or ClassName
     * @param string $className
     * @return NewRoute
     */
    public function mw(string $className): NewRoute
    {
        $this->middleware[] = $className;
        return $this;
    }
}
