<?php

namespace app\Core\Websockets\Protocols;

use app\Core\Request\ParameterBag;

class HeaderBag extends ParameterBag implements \ArrayAccess
{

    protected string $headers;

    public function __construct(string $headers)
    {
        $this->headers = $headers;
        parent::__construct($this->getHeaders());
    }

    protected function getHeaders(): array
    {
        $return = [];
        foreach (explode("\r\n", $this->headers) as $header) {
            $parts = explode(': ', $header, 2);
            if (count($parts) == 2) {
                [$name, $value] = $parts;
                if (!isset($return[$name])) {
                    $return[$name] = $value;
                } else {
                    if (is_array($return[$name])) {
                        $return[$name][] = $value;
                    } else {
                        $return[$name] = [$return[$name], $value];
                    }
                }
            }
        }

        return array_change_key_case($return);
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
}
?>