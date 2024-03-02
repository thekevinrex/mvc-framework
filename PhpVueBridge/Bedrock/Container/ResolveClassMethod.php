<?php


namespace PhpVueBridge\Bedrock\Container;

class ResolveClassMethod extends ParameterResolver
{

    public function call($class, $method, $param = [])
    {
        if (is_array($method)) {
            [$class, $method] = $method;
        }

        $parameter = $this->resolveParameters($class, $method, $param);

        if (method_exists($class, 'callAction')) {
            return $class->callAction($method, $parameter);
        }

        return call_user_func([$class, $method], ...array_values($parameter));
    }

    protected function resolveParameters($class, $method, $param = [])
    {
        return $this->resolveClassMethodParameter($param, $class, $method);
    }
}