<?php

namespace PhpVueBridge\Http\Request;

class ParameterBag implements \IteratorAggregate, \Countable
{

    /**
     * The array of elements
     */
    protected $parameters;

    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    public function set($key, $value): self
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    public function has($key): bool
    {
        return isset($this->parameters[$key]);
    }

    public function get($key, $default = null): mixed
    {
        if (!$this->has($key))
            return $default;

        return $this->parameters[$key];
    }

    /**
     * Returns an iterator for parameters.
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->parameters);
    }

    /**
     * Returns the number of parameters.
     */
    public function count(): int
    {
        return \count($this->parameters);
    }

}
?>