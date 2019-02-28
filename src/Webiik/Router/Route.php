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
     * @var array
     */
    private $regex = [];

    /**
     * @var bool
     */
    private $sensitive = false;

    /**
     * @var string
     */
    private $controller = '';

    /**
     * @var array
     */
    private $middleware = [];

    /**
     * @var array
     */
    private $httpMethods = [];

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
     * After calling getURL or getURI, this is filled with array of missing route parameters:
     * [int parameterPosition => string parameterRegex], ...
     * @var array
     */
    private $missingParameters = [];

    /**
     * @var string
     */
    private $baseURI = '';

    /**
     * @var string
     */
    private $server = '';

    /**
     * Route constructor.
     * @param array $httpMethods
     * @param array $regex
     * @param string $controller
     * @param string $name
     * @param string $lang
     * @param array $mw
     * @param bool $sensitive
     * @param array $parameters
     * @param string $baseURI
     * @param string $server
     */
    public function __construct(
        array $httpMethods,
        array $regex,
        string $controller,
        string $name,
        string $lang,
        array $mw,
        bool $sensitive,
        array $parameters,
        string $baseURI,
        string $server
    ) {
        $this->httpMethods = $httpMethods;
        $this->regex = $regex;
        $this->controller = $controller;
        $this->name = $name;
        $this->lang = strtolower($lang);
        $this->middleware = $mw;
        $this->sensitive = $sensitive;
        $this->parameters = $parameters;
        $this->baseURI = $baseURI;
        $this->server = $server;
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->httpMethods;
    }

    /**
     * @param string $lang
     * @return string
     */
    public function getRegex(string $lang = ''): string
    {
        if ($lang) {
            $regex = isset($this->regex[$lang]) ? $this->regex[$lang] : '';
        } else {
            $regex = $this->regex[$this->lang];
        }
        return $regex;
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
     * @return bool
     */
    public function isSensitive(): bool
    {
        return $this->sensitive;
    }

    /**
     * Get parameters injected during Route construction
     * [int regexGroupPosition|regexGroupName => string|int parameterValue]
     * e.g. ['name' => 'dolly', '1' => 'dolly', '2' => 'hello']
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get route regex parameters
     * [int parameterPosition => string parameterRegex], ...
     * e.g. ['0' => '(?<name>[a-z]*)?', '1' => '([a-z]*)']
     * @param string $lang
     * @return array
     */
    public function getRegexParameters(string $lang = ''): array
    {
        preg_match_all('~\(.+?\)\??~', $this->getRegex($lang), $matches);
        return $matches ? $matches[0] : [];
    }

    /**
     * Get missing parameters after calling getURI or getURL
     * [int parameterPosition => string parameterRegex], ...
     * e.g. ['1' => '([a-z]*)']
     * @return array
     */
    public function getMissingParameters(): array
    {
        return $this->missingParameters;
    }

    /**
     * Get URI of this route
     * @param array $parameters Array with parameter values
     * @param string $lang
     * @return string
     */
    public function getURI(array $parameters = [], string $lang = ''): string
    {
        // If $parameters are empty use injected parameters instead
        $parameters = $parameters ? $parameters : $this->parameters;

        // Reset missing route parameters before every getURI call
        $this->missingParameters = [];

        // Remove named parameters
        $parameters = $this->reduceParameters($parameters);

        $paramPos = 0;

        // Replace regex groups(parameters) with values
        $URI = preg_replace_callback('~(\(.+?\))(\?)?(/)~', function ($match) use (&$parameters, &$paramPos) {

            // Determine if parameter is required
            $paramIsRequired = $match[2] ? false : true;

            // Get value for current regex group(parameter)
            $replacement = current($parameters);

            // Move array pointer to next value
            next($parameters);

            // Add missing parameter to missingParameters.
            // It allows user to get missing parameters after getURI call.
            if (!$replacement && $paramIsRequired) {
                $this->missingParameters[$paramPos] = rtrim($match[0], '/');
            }

            // In preg_replace_callback we replace slash after regex group.
            // We have to add this slash back. But in case of optional regex group and
            // empty replacement, we don't add it back to prevent double slashes '//'.
            $slash = !$paramIsRequired && !$replacement ? '' : '/';

            $paramPos++;

            return $replacement . $slash;
        }, $this->getRegex($lang));

        if ($this->missingParameters || !$URI) {
            // If URI parameters are missing, return empty URI
            $URI = '';
        } else {
            // Remove regex special chars to get clean URI
            $URI = $this->baseURI . preg_replace('/[~\^\?\$]|i$/', '', $URI);
        }

        return $URI;
    }

    /**
     * Get URL of this route
     * @param array $parameters
     * @param string $lang
     * @return string
     */
    public function getURL(array $parameters = [], string $lang = ''): string
    {
        return $this->server . $this->getURI($parameters, $lang);
    }

    /**
     * Remove named parameters
     * @param array $parameters
     * @return array
     */
    private function reduceParameters(array $parameters): array
    {
        foreach ($parameters as $key => $param) {
            if (is_string($key)) {
                unset($parameters[$key]);
            }
        }
        return $parameters;
    }
}
