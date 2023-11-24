<?php


namespace app\core\view;


use app\core\interfaces\Renderable;

abstract class Component
{

    const ALLOWED_TYPES = ['int', 'string', 'bool'];

    public function _initialize(array $attributes = [])
    {
        $properties = $this->getProperties();

        foreach ($properties as $name => $type) {
            if (in_array($name, array_keys($attributes))) {

                if (!is_null($type) && gettype($attributes[$name]) !== $type) {
                    continue;
                }

                $this->{$name} = $attributes[$name];
            }
        }
    }

    public function getProperties(): array
    {
        $properties_all = (new \ReflectionClass($this))->getProperties();

        $properties = [];

        foreach ($properties_all as $property) {
            if ($property->isPublic()) {

                if ($property->hasType() && $property->hasDefaultValue()) {
                    continue;
                }

                if ($property->hasType() && $property->isInitialized($this)) {
                    continue;
                }

                $type = $property->getType();

                if (!is_null($type) && (!$type instanceof \ReflectionNamedType || !$type->isBuiltin())) {
                    continue;
                }

                $properties[$property->getName()] = (is_null($type))
                    ? null
                    : $type->getName();
            }
        }

        return $properties;
    }

    public function getPropertiesValues(): array
    {
        $properties_all = (new \ReflectionClass($this))->getProperties();

        $properties = [];

        foreach ($properties_all as $property) {
            if ($property->isPublic()) {
                $properties[$property->getName()] = ($property->isInitialized($this))
                    ? $property->getValue($this)
                    : null;
            }
        }

        return $properties;
    }

    abstract public function render();

    public function shouldRender()
    {
        return true;
    }
}