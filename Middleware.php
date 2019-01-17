<?php
declare(strict_types=1);

namespace Webiik\Middleware;

use Webiik\Container\Container;
use Webiik\Data\Data;

class Middleware
{
    /**
     * Use Container to allow DI from Container to middleware constructor
     * @var Container
     */
    private $container;

    /**
     * @var array
     */
    private $middleware = [];

    /**
     * Index of current middleware.
     * It's more efficient to use $index helper than to use next to shift array pointer position.
     * @var int
     */
    private $index = 0;


    /**
     * @var Data
     */
    private $middlewareData;

    /**
     * Middleware constructor.
     * @param Container $container
     * @param Data $data
     */
    public function __construct(Container $container, Data $data)
    {
        $this->container = $container;
        $this->middlewareData = $data;
    }

    /**
     * Add middleware
     * @param string $controller 'className:methodName'
     * @param array $data key-value array
     */
    public function add(string $controller, $data = []): void
    {
        $this->middleware[] = [
            'controller' => $controller,
            'data' => $data
        ];
    }

    /**
     * Run middleware
     * @throws \ReflectionException
     */
    public function run(): void
    {
        if (isset($this->middleware[$this->index])) {
            list($controller, $method) = $this->getClassMethod($this->middleware[$this->index]['controller']);
            if (method_exists($controller, '__construct')) {
                $middleware = new $controller(...$this->container->injectTo($controller));
            } else {
                $middleware = new $controller();
            }
            if ($this->middleware[$this->index]['data']) {
                foreach ($this->middleware[$this->index]['data'] as $key => $val) {
                    $this->middlewareData->set($key, $val);
                }
            }
            $middleware->$method($this->next(), $this->middlewareData);
        }
    }

    /**
     * Get launcher of next middleware
     * @return \Closure
     */
    private function next(): callable
    {
        $next = function () {
            $this->index++;
            $this->run();
        };
        return $next;
    }

    /**
     * Get array with name of class and method from className:methodName
     * @param string $controller
     * @return array
     */
    private function getClassMethod(string $controller)
    {
        $controller = explode(':', $controller);
        return $controller ? $controller : [];
    }
}
