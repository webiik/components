<?php
declare(strict_types=1);

namespace Webiik\Container;

class Container extends \Pimple\Container
{
    /**
     * Add service to container
     * @param string $name
     * @param callable $factory
     */
    public function addService(string $name, callable $factory): void
    {
        $this[$name] = $factory;
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
     * Inject dependencies from Container to $object using the object constructor method
     * @param string $className
     * @return array
     * @throws \ReflectionException
     */
    public function injectTo(string $className): array
    {
        return $this->prepareMethodParameters($className, '__construct');
    }

    /**
     * Get an array with parameters described in method's doc comment
     * @param \ReflectionMethod $reflection
     * @return array
     */
    private function getDocBlockParams(\ReflectionMethod $reflection): array
    {
        $methodDocBlockParams = [];
        $methodDocBlock = $reflection->getDocComment();
        if ($methodDocBlock) {
            preg_match_all('/@param\s([\w_\\-]+)?\s?\$([\w_\\-]+)/', $methodDocBlock, $matches);
            foreach ($matches[1] as $index => $match) {
                // arr['paramName'] = 'paramType'
                $methodDocBlockParams[$matches[2][$index]] = $match;
            }
        }
        return $methodDocBlockParams;
    }

    /**
     * @param string $className
     * @param string $methodName
     * @return array
     * @throws \ReflectionException
     * @throws \Exception
     */
    private function prepareMethodParameters(string $className, string $methodName): array
    {
        $methodParamInstances = [];
        $reflection = new \ReflectionMethod($className, $methodName);

        // Get an array with parameters described in method's doc comment
        $methodDocBlockParams = $this->getDocBlockParams($reflection);

        // Get instances of required parameters from container
        $methodParams = $reflection->getParameters();
        foreach ($methodParams as $methodParam) {
            // Get service name
            $serviceName = $methodParam->getName();
            $serviceType = $methodParam->getType();

            // If service type is a class, use class name or doc block paramType as service name
            if ($serviceType) {

                if ($serviceType instanceof \ReflectionUnionType) {
                    throw new \Exception('Class: Container, Service parameter `$' . $serviceName . '` can\'t be union type.');
                }

                if ($serviceType instanceof \ReflectionNamedType && !$serviceType->isBuiltin()) {
                    $serviceName = $serviceType->getName();

                    // If param type is described in method's doc block
                    // use as service name param type from doc block instead of class name.
                    // Also Prevent to update mismatched params, eg. param name
                    // is different in doc block than in method definition.
                    if (isset($methodDocBlockParams[$methodParam->getName()])) {
                        // Update only params with paramType starting with lower case 'ws'
                        if (preg_match('/^ws[A-Z]/', $methodDocBlockParams[$methodParam->getName()])) {
                            $serviceName = $methodDocBlockParams[$methodParam->getName()];
                        }
                    }
                }
            }

            if ($serviceName == 'Webiik\Container\Container') {
                $methodParamInstances[] = $this;
            } elseif ($methodParam->isDefaultValueAvailable() && !$this->isIn($serviceName)) {
                $methodParamInstances[] = $methodParam->getDefaultValue();
            } else {
                $methodParamInstances[] = $this->get($serviceName);
            }
        }

        return $methodParamInstances;
    }
}
