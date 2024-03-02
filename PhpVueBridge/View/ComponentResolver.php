<?php

namespace PhpVueBridge\View;

use Closure;

class ComponentResolver
{

    public static Closure $componentResolver;

    protected array $contructorParametersCache = [];

    public function get(
        string $class,
        ComponentAttributeBag $attributes,
        array $data = [],
    ) {

        if (isset(self::$componentResolver)) {
            return call_user_func(self::$componentResolver, $class, $attributes, $data);
        }

        $parameters = $this->extractContructorParameters($class);

        foreach ($parameters as $parameter) {
            if ($attributes->has($parameter)) {
                $data[$parameter] = $attributes->get($parameter);
            }
        }

        $component = app($class, $data);

        return $component;
    }

    protected function extractContructorParameters(string $className)
    {

        if (isset($this->contructorParametersCache[$className])) {
            return $this->contructorParametersCache[$className];
        }

        $class = new \ReflectionClass($className);

        $contructor = $class->getConstructor();

        return $this->contructorParametersCache[$className] = $contructor
            ? array_map(fn ($parameter) => $parameter->getName(), $contructor->getParameters())
            : [];
    }
}
