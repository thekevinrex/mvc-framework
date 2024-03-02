<?php

namespace PhpVueBridge\Bedrock\Container;


use PhpVueBridge\Support\Util;
use Closure;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

class ClassBuilder
{

    protected array $buildings = [];

    protected array $buildingParameters = [];


    public function __construct(
        protected Container $container
    ) {

    }

    /**
     * Check if a given class with his callback is buildable
     * 
     * @param mixed $key
     * @param mixed $callback
     * @return bool
     */
    public function isBuildable($key, $callback): bool
    {
        return $key === $callback || $callback instanceof Closure;
    }

    /**
     * Summary of build
     * @param mixed $callback
     * @param mixed $param
     * @throws \Exception
     * @return mixed
     */
    public function build($callback)
    {

        if ($callback instanceof Closure) {
            return call_user_func($callback, $this, ...end($this->buildingParameters));
        }

        try {
            $reflector = new ReflectionClass($callback);
        } catch (ReflectionException $e) {
            throw new Exception("Target class [$callback] does not exist.", 0, $e);
        }

        if (!$reflector->isInstantiable()) {
            throw new Exception("Target [$callback] is not instantiable.");
        }

        $this->buildings[] = $callback;

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            array_pop($this->buildings);
            return new $callback();
        }

        $dependencies = $constructor->getParameters();

        // Once we have all the constructor's parameters we can create each of the
        // dependency instances and then use the reflection instances to make a
        // new instance of this class, injecting the created dependencies in.

        try {
            $instances = $this->resolveDependencies($dependencies);
        } catch (Exception $e) {
            array_pop($this->buildings);
            throw $e;
        }

        array_pop($this->buildings);

        return $reflector->newInstanceArgs($instances);
    }

    /**
     * Resolve the dependecies of the given class
     * @param array $dependencies
     * @param array $param
     * @return array
     */
    protected function resolveDependencies(array $dependencies): array
    {
        $results = [];

        $param = end($this->buildingParameters);

        foreach ($dependencies as $dependency) {

            if (
                array_key_exists(
                    $dependency->name,
                    $param
                )
            ) {
                $results[] = $param[$dependency->name];
                continue;
            }

            $result =
                is_null(Util::getParameterClassName($dependency))
                ? $this->resolvePrimitive($dependency)
                : $this->resolveClass($dependency);

            if ($dependency->isVariadic()) {
                $results = array_merge($results, $result);
            } else {
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * Resolve a primitive dependency for the class
     * @param \ReflectionParameter $parameter
     * @throws \Exception
     * @return mixed
     */
    protected function resolvePrimitive(ReflectionParameter $parameter)
    {

        if (!is_null($callback = $this->getContextualCallback('$' . $parameter->getName()))) {
            return ($callback instanceof Closure) ? $callback($this) : $callback;
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->isVariadic()) {
            return [];
        }

        return null;
    }

    /**
     * Resolve a class based dependency from the container.
     *
     * @param  \ReflectionParameter  $parameter
     * @return mixed
     * @throws Exception
     */
    protected function resolveClass(ReflectionParameter $parameter)
    {
        try {
            return $parameter->isVariadic()
                ? $this->resolveVariadicClass($parameter)
                : $this->resolve(Util::getParameterClassName($parameter));
        } catch (Exception $e) {

            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            if ($parameter->isVariadic()) {
                return [];
            }

            throw $e;
        }
    }

    /**
     * Resolve a variadic class from the container
     * 
     * @param \ReflectionParameter $parameter
     * @return array
     */
    protected function resolveVariadicClass(ReflectionParameter $parameter): array
    {

        $className = Util::getParameterClassName($parameter);

        $key = $this->getAlias($className);

        if (!is_array($callback = $this->getContextualCallback($key))) {
            return $this->resolve($className);
        }

        return array_map(function ($key) {
            return $this->resolve($key);
        }, $callback);
    }
}

?>