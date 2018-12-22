<?php
declare(strict_types=1);

namespace Webiik\Container;

class Container extends \Pimple\Container
{
    /**
     * Add service to container
     * @param string $name
     * @param callable $callable
     */
    public function addService(string $name, callable $callable): void
    {
        $this[$name] = $callable;
    }

    /**
     * Add service factory to container
     * @param string $name
     * @param callable $factory
     */
    public function addServiceFactory(string $name, callable $factory): void
    {
        $this[$name] = $this->factory($factory);
    }

    /**
     * Add value to container
     * @param string $name
     * @param mixed $val
     */
    public function addParam(string $name, $val): void
    {
        $this[$name] = $val;
    }

    /**
     * Add function to container
     * @param string $name
     * @param callable $function
     */
    public function addFunction(string $name, callable $function): void
    {
        $this[$name] = $this->protect($function);
    }

    /**
     * Get value from container
     * @param string $name
     * @return mixed
     */
    public function get(string $name)
    {
        return $this[$name];
    }

    /**
     * Check if value is in container
     * @param string $name
     * @return bool
     */
    public function isIn(string $name): bool
    {
        return isset($this[$name]);
    }

    /**
     * Inject dependencies from Container to $object using object constructor method
     * @param string $className
     * @param Container $container
     * @return array
     * @throws \ReflectionException
     */
    public static function inject(string $className, Container $container): array
    {
        return self::prepareMethodParameters($className, '__construct', $container);
    }

    /**
     * @param string $className
     * @param string $methodName
     * @param Container $container
     * @return array
     * @throws \ReflectionException
     */
    private static function prepareMethodParameters(string $className, string $methodName, Container $container): array
    {
        $methodParamInstances = [];

        $reflection = new \ReflectionMethod($className, $methodName);
        $methodParams = $reflection->getParameters();
        foreach ($methodParams as $methodParam) {
            if ($methodParam->getClass()) {
                // Parameter is a class
                $methodParamName = $methodParam->getClass()->getName();
            } else {
                // Parameter is not a class
                $methodParamName = $methodParam->getName();
            }

            if ($methodParam->isOptional()) {
                if ($container->isIn($methodParamName)) {
                    $methodParamInstances[] = $container->get($methodParamName);
                } else {
                    $methodParamInstances[] = $methodParam->getDefaultValue();
                }
            } else {
                $methodParamInstances[] = $container->get($methodParamName);
            }
        }

        return $methodParamInstances;
    }
}
