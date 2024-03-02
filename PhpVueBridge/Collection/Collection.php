<?php

namespace PhpVueBridge\Collection;

use PhpVueBridge\Collection\Interfaces\CollectionInterface;


class Collection implements \ArrayAccess, CollectionInterface
{

    protected $items;

    public function __construct($items = [])
    {
        $this->items = $items;
    }

    public function merge(array $newItems = []): self
    {
        $this->items = array_merge($this->items, $newItems);

        return $this;
    }

    public function has($key)
    {
        return isset($this->items[$key]);
    }

    public function getNestedVal($items, $key, $default)
    {

        $pos = strpos($key, ".");

        if (!$pos) {
            return (isset($items[$key])) ? $items[$key] : $default;
        }

        if (isset($items[substr($key, 0, $pos)])) {
            return $this->getNestedVal($items[substr($key, 0, $pos)], substr($key, $pos + 1, (strlen($key) - ($pos + 1))), $default);
        }

        return $default;
    }

    public function get($key, $default = null)
    {

        if ($this->has($key)) {
            return $this->items[$key];
        }

        $pos = strpos($key, ".");

        if ($pos) {
            return $this->getNestedVal($this->items, $key, $default);
        }

        return $default;
    }

    public function set($key, $value = null)
    {
        $this->items[$key] = $value;
    }

    /**
     * Determine if the given configuration option exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    /**
     * Get a configuration option.
     *
     * @param  string  $key
     * @return mixed
     */
    public function offsetGet($key): mixed
    {
        return $this->get($key);
    }

    /**
     * Set a configuration option.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Unset a configuration option.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key): void
    {
        $this->set($key, null);
    }

    public function toArray()
    {
        return $this->items;
    }
}

?>