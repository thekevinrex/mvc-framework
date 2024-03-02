<?php


namespace PhpVueBridge\View;

use PhpVueBridge\Collection\Collection;


class ComponentAttributeBag extends Collection implements \Stringable
{

    protected array $attributes = [];

    protected array $props = [];

    protected array $classes;

    public function __construct($items = [])
    {
        parent::__construct($items);

        $this->attributes = $items;

        if (in_array('class', array_keys($items))) {
            $this->resolveClassAttribute($items['class']);
        }
    }

    protected function resolveClassAttribute($classString)
    {
        $this->classes = explode(' ', $classString);
    }

    public function mergeClass($class)
    {

        if (!is_array($class)) {
            $class = [$class];
        }

        return (implode(' ', array_merge($this->classes, $class)));
    }

    public function __toString()
    {
        return implode(' ', $this->attributesToString());
    }

    public function prop(string $prop)
    {
        $value = $this->get($prop, false);

        if (str_starts_with($prop, ':') || !$value || str_starts_with($value, ':')) {
            return null;
        }

        unset($this->attributes[$prop]);
        $this->props[] = $prop;

        return $value;
    }

    protected function attributesToString()
    {
        $attributes = [];

        foreach ($this->attributes as $key => $value) {

            if (str_starts_with($key, 'vue:')) {
                $key = str_replace('vue:', '', $key);
            }

            $attributes[] = "{$key}='{$value}'";
        }

        return $attributes;
    }

    public function toVueProps(): array
    {
        $props = array();

        foreach ($this->attributes as $key => $value) {
            if (str_starts_with($key, 'vue:')) {
                $key = str_replace('vue:', '', $key);
            }

            $props[] = $key;
        }

        return $props;
    }
    public function toVuePropsWithValue(): array
    {
        $props = array();

        foreach ($this->attributes as $key => $value) {
            if (str_starts_with($key, 'vue:')) {
                $key = str_replace('vue:', '', $key);
            }

            $props[$key] = $value;
        }

        return $props;
    }

    public function getPropsAsData(): array
    {
        $props = array();

        foreach ($this->props as $value) {
            $props[$value] = $this->get($value);
        }

        return $props;
    }
}