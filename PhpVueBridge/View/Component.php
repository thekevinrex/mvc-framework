<?php


namespace PhpVueBridge\View;

abstract class Component
{

    const ALLOWED_TYPES = ['int', 'string', 'bool'];

    public string $viewName;

    public ComponentAttributeBag $attributes;

    protected array $except = [];

    public function _initialize(array $attributes)
    {

        $class = get_class($this);

        $properties = (new \ReflectionClass($class))->getProperties();

        foreach ($properties as $property) {
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

                $name = $property->getName();

                $type = (is_null($type))
                    ? null
                    : $type->getName();

                if (in_array($name, array_keys($attributes))) {

                    if (!is_null($type) && gettype($attributes[$name]) !== $type) {
                        continue;
                    }

                    $this->{$name} = $attributes[$name];
                }
            }
        }
    }

    public function _getData(): array
    {
        return array_merge(
            ['attributes' => $this->attributes ?? []],
            $this->_getPublicPropertiesValues(),
            $this->_getPublicMethods(),
        );
    }

    public function _getPublicProperties(): array
    {
        $class = get_class($this);
        $properties_all = (new \ReflectionClass($class))->getProperties();

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

    public function _getPublicPropertiesValues(): array
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

    public function _getPublicMethods(): array
    {
        $methods = (new \ReflectionClass($this))->getMethods();

        $publicMethods = [];
        $except = $this->ignoreMethods();

        foreach ($methods as $method) {

            $name = $method->getName();

            if ($method->isPublic() && !str_starts_with($name, '_') && !in_array($name, $except)) {
                $publicMethods[$name] = function (...$data) use ($name) {
                    return $this->{$name}(...$data);
                };
            }
        }

        return $publicMethods;
    }

    protected function ignoreMethods(): array
    {
        return array_merge(
            [
                'shouldRender',
                'ignoreMethods',
                'render',
                'withName',
                'withAttributes',
                'resolveView',
            ],
            $this->except
        );
    }

    public function resolveView(): string|View
    {
        $view = $this->render();

        // TODO: Implement resolveView method for view class

        return $view;
    }

    abstract public function render(): string|View;

    public function shouldRender()
    {
        return true;
    }

    public function withName(string $name): void
    {
        $this->viewName = $name;
    }

    public function withAttributes(ComponentAttributeBag $attributes): void
    {
        $this->attributes = $attributes;
    }
}
