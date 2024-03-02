<?php


namespace PhpVueBridge\Bedrock\Container;


class ResolveClosure extends ParameterResolver
{

    public function call($function, $param = [])
    {
        return call_user_func($function, ...array_values($this->resolveParameters($function, $param)));
    }

    protected function resolveParameters($function, $param)
    {
        return $this->resolveMethodParameters($param, new \ReflectionFunction($function));
    }
}