<?php


namespace PhpVueBridge\Routes;

use PhpVueBridge\Bedrock\Container\ParameterResolver;


class ClosureDispatcher extends ParameterResolver
{

    public function dispatch(Route $route, \Closure $function)
    {
        return call_user_func($function, ...array_values($this->resolveParameters($route, $function)));
    }

    protected function resolveParameters(Route $route, $function)
    {
        return $this->resolveMethodParameters($route->getParameters(), new \ReflectionFunction($function));
    }
}