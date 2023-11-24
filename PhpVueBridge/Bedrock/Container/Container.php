<?php

namespace PhpVueBridge\Bedrock\Container;

use PhpVueBridge\Bedrock\Contracts\ContainerContract;
use Closure;
use Exception;
use TypeError;

/**
 * 
 * Bind the clases to the container and resolve those clases when is need 
 * Is the responsable of making the dependecy injections to the controllers, middleware and others clases
 */
class Container extends ClassBuilder implements ContainerContract
{

    /**
     * The shared instances
     */
    protected array $instances = [];

    /**
     * The resolved clases
     */
    protected array $resolved = [];

    /**
     * The contract and interfeces binded
     */
    protected array $bindings = [];

    /**
     * The methods binded
     */
    protected array $methodsBindings = [];

    /**
     * The contextual bindings
     */
    protected array $contextual = [];

    /**
     * The aliases ($class => $key)
     */
    protected array $aliases = [];

    /**
     * The key and his aliases ($key[] => $aliases)
     */
    protected array $keyAliases = [];

    /**
     * Bind a class with his interface to the container
     * @param mixed $callback
     */
    public function bind(string $key, $callback = null, bool $shared = false): void
    {
        $this->dropStaleInstances($key);

        if (is_null($callback)) {
            $callback = $key;
        }

        if (!$callback instanceof Closure) {
            if (!is_string($callback)) {
                throw new TypeError(self::class . '::bind(): Argument #2 ($callback) must be of type Closure|string|null');
            }

            $callback = $this->getClosure($key, $callback);
        }

        $this->bindings[$key] = compact('callback', 'shared');
    }

    public function bindIf(string $key, $callback = null, bool $shared = false): void
    {
        if (!$this->bounded($key)) {
            $this->bind($key, $callback, $shared);
        }
    }

    /**
     * Bind a shared class
     * @param mixed $callback
     */
    public function bindShared(string $key, $callback = null): void
    {
        $this->bind($key, $callback, true);
    }

    public function bindSharedIf(string $key, $callback = null): void
    {
        if (!$this->bounded($key)) {
            $this->bindShared($key, $callback);
        }
    }

    /**
     * Save the shared instanced clases for use when is neded
     */
    public function instance(string $key, $instance): mixed
    {
        // If the class has prevesly binded its removed
        $this->removeKeyAlias($key);

        unset($this->aliases[$key]);

        $this->instances[$key] = $instance;

        return $instance;
    }

    public function when($classes)
    {
        #todo implement contextual binding

        $keys = [];

        $classes = is_array($classes) ? $classes : [$classes];

        foreach ($classes as $class) {
            $keys[] = $this->getAlias($class);
        }

        $this->contextual[] = $keys;
    }

    public function addContextualBinding($concrete, $abstract, $implementation)
    {
        $this->contextual[$concrete][$this->getAlias($abstract)] = $implementation;
    }

    /**
     * Check if a method is binded to the container
     */
    public function hasMethodBinding($method): bool
    {
        return isset($this->methodsBindings[$method]);
    }

    /**
     * Bind a method to the container
     */
    public function bindMethod($method, $callback): void
    {
        if (is_array($method)) {
            $this->methodsBindings[$method[0] . '@' . $method[1]] = $callback;
        } else {
            $this->methodsBindings[$method] = $callback;
        }
    }

    /**
     * Call a method binded to the container
     */
    public function callMethodBinding($method, $parameters = []): mixed
    {
        return $this->call($this->methodsBindings[$method], [$this, ...$parameters]);
    }

    /**
     * Resolve the given class in the container
     *
     * In here if a class is not resolved the container will build that class and make the corresponding
     * dependency injection to the class constructor
     */
    public function resolve(string $class, array $param = [], bool $events = false): mixed
    {
        $key = $this->getAlias($class);

        $callback = $this->getContextualCallback($key);

        $needsContextualBuild = !empty($param) || !is_null($callback);

        if ((isset($this->instances[$key]) || isset($this->instances[$class])) && !$needsContextualBuild) {
            return $this->instances[$key] ?? $this->instances[$class];
        }

        $this->buildingParameters[] = $param;

        if (is_null($callback))
            $callback = $this->getCallback($key);

        if ($this->isBuildable($key, $callback)) {
            $object = $this->build($callback);
        } else {
            $object = $this->resolve($callback);
        }

        if ($this->isShared($key) && !$needsContextualBuild) {
            $this->instances[$key] = $object;
        }

        $this->resolved[get_class($object)] = true;
        array_pop($this->buildingParameters);

        return $object;
    }


    /**
     * Wrap a callback into a clousure when a string is provided when bind a class to the container
     */
    protected function getClosure(string $key, string $class): Closure
    {
        return function ($app, $param = []) use ($key, $class) {
            if ($key === $class) {
                return $app->build($class);
            }

            return $app->resolve($class, $param, false);
        };
    }

    /**
     * get the callback for the given key
     */
    protected function getCallback(string $key): Closure|string
    {
        if (isset($this->bindings[$key]))
            return $this->bindings[$key]['callback'];

        return $key;
    }

    /**
     * Summary of getContextualCallback
     * @return Closure|string|null
     */
    protected function getContextualCallback(string $key)
    {
        if (!is_null($callback = $this->findInContextualBindings($key))) {
            return $callback;
        }

        if (empty($this->keyAliases[$key])) {
            return null;
        }

        foreach ($this->keyAliases[$key] as $alias) {
            if (!is_null($callback = $this->findInContextualBindings($alias))) {
                return $callback;
            }
        }

        return null;
    }

    /**
     * Find the concrete binding for the given abstract in the contextual binding array.
     *
     * @return \Closure|string|null
     */
    protected function findInContextualBindings(string $key)
    {
        return $this->contextual[end($this->buildings)][$key] ?? null;
    }

    /**
     * Check if the given key is bounded to the container
     */
    public function bounded(string $key): bool
    {
        return isset($this->instance[$key]) || $this->bindings[$key] || $this->isAlias($key);
    }

    /**
     * Check if a given key is resolved in the container
     */
    public function resolved(string $key): bool
    {
        if ($this->isAlias($key)) {
            $key = $this->getAlias($key);
        }

        return isset($this->resolved[$key]) || isset($this->instances[$key]);
    }

    /**
     * Check if the given key is a binded shared
     */
    public function isShared(string $key): bool
    {
        return isset($this->instances[$key]) ||
            (isset($this->bindings[$key]['shared']) && $this->bindings[$key]['shared'] === true);
    }

    /**
     * Check if a given key is a aliases
     */
    public function isAlias(string $key): bool
    {
        return isset($this->aliases[$key]);
    }

    /**
     * Get the alias for the given key
     */
    public function getAlias(string $key): string
    {
        return $this->isAlias($key)
            ? $this->getAlias($this->aliases[$key])
            : $key;
    }

    /**
     * Set a aliases in the container
     * @param mixed $key
     * @param mixed $class
     * @return void
     */
    public function setAlias(string $key, string $class): void
    {
        if ($key === $class) {
            throw new \LogicException("[{$key}] is aliased to itself.");
        }

        $this->aliases[$class] = $key;
        $this->keyAliases[$key][] = $class;
    }

    /**
     * Remove a abstract aliases given a key
     */
    protected function removeKeyAlias(string $searched): void
    {
        if (!isset($this->aliases[$searched])) {
            return;
        }

        foreach ($this->keyAliases as $key => $aliases) {
            foreach ($aliases as $index => $alias) {
                if ($alias == $searched) {
                    unset($this->keyAliases[$key][$index]);
                }
            }
        }
    }

    /**
     * Drop the shared instance and the aliases of the given key
     */
    protected function dropStaleInstances(string $key): void
    {
        unset($this->instances[$key], $this->aliases[$key]);
    }

    /**
     * Call a given method or closure with the given arguments
     */
    public function call($method, $class = null, array $param = [])
    {
        if ($method instanceof Closure) {
            return (new ResolveClosure($this))->call($method, $param);
        } else {
            return (new ResolveClassMethod($this))->call($class, $method, $param);
        }
    }

}

?>