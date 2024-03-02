<?php


namespace PhpVueBridge\Routes;

use PhpVueBridge\Bedrock\Container\ParameterResolver;

class ControllerResolver extends ParameterResolver
{


    public function resolve(Route $route, $object, $method)
    {
        $parameter = $this->resolveParameters($route, $object, $method);

        if (method_exists($object, 'callAction')) {
            return $object->callAction($method, $parameter);
        }

        return call_user_func([$object, $method], ...array_values($parameter));
    }

    protected function resolveParameters(Route $route, $object, $method)
    {
        return $this->resolveClassMethodParameter($route->getParameters(), $object, $method);
    }
}