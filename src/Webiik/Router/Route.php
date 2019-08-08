<?php
declare(strict_types=1);

namespace Webiik\Router;

class Route
{
    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $controller = '';

    /**
     * @var array
     */
    private $middleware = [];

    /**
     * @var string
     */
    private $lang = '';

    /**
     * Parameters injected during Route construction
     * @var array
     */
    private $parameters = [];

    /**
     * Route constructor.
     * @param string $controller
     * @param string $name
     * @param string $lang
     * @param array $mw
     * @param array $parameters
     */
    public function __construct(
        string $controller,
        string $name,
        string $lang,
        array $mw,
        array $parameters
    ) {
        $this->controller = $controller;
        $this->name = $name;
        $this->lang = strtolower($lang);
        $this->middleware = $mw;
        $this->parameters = $parameters;
    }

    /**
     * Get array with name of class and method from className:methodName
     * @return array
     */
    public function getController(): array
    {
        $controller = explode(':', $this->controller);
        return $controller ? $controller : [];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * @return array
     */
    public function getMw(): array
    {
        return $this->middleware;
    }

    /**
     * Get parameters injected during Route construction
     * e.g. ['name' => 'dolly', '1' => 'hello']
     * @return array
     */
    public function getParameters(): array
    {
        $params = [];
        $skip = false;
        $index = 0;

        // Remove named parameters associated with key indexes and reindex the array
        foreach ($this->parameters as $key => $val) {
            if ($skip) {
                $skip = false;
                continue;
            }
            if (is_string($key)) {
                $params[$key] = $val;
                $skip = true;
            } else {
                $params[$index] = $val;
            }
            $index++;
        }

        return $params;
    }
}
