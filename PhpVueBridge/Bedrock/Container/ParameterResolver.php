<?php


namespace PhpVueBridge\Bedrock\Container;

use PhpVueBridge\Bedrock\Application;
use PhpVueBridge\Support\Util;
use ReflectionClass;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use stdClass;

class ParameterResolver
{

    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function resolveClassMethodParameter(array $parameters, $object, string $method)
    {
        if (!method_exists($object, $method)) {
            return $parameters;
        }

        return $this->resolveMethodParameters($parameters, new \ReflectionMethod($object, $method));
    }

    public function resolveMethodParameters(array $parameters, ReflectionFunctionAbstract $reflector)
    {

        $instanceCount = 0;

        $values = array_values($parameters);

        $skippableValue = new stdClass;

        foreach ($reflector->getParameters() as $key => $parameter) {
            $instance = $this->transformDependency($parameter, $parameters, $skippableValue);

            if ($instance !== $skippableValue) {
                $instanceCount++;

                $this->spliceIntoParameters($parameters, $key, $instance);
            } elseif (
                !isset($values[$key - $instanceCount]) &&
                $parameter->isDefaultValueAvailable()
            ) {
                $this->spliceIntoParameters($parameters, $key, $parameter->getDefaultValue());
            }
        }

        return $parameters;
    }

    protected function transformDependency(ReflectionParameter $parameter, $parameters, $skippableValue)
    {
        $className = Util::getParameterClassName($parameter);

        // If the parameter has a type-hinted class, we will check to see if it is already in
        // the list of parameters. If it is we will just skip it as it is probably a model
        // binding and we do not want to mess with those; otherwise, we resolve it here.
        if ($className && !$this->alreadyInParameters($className, $parameters)) {
            $isEnum = method_exists(ReflectionClass::class, 'isEnum') && (new ReflectionClass($className))->isEnum();

            return $parameter->isDefaultValueAvailable()
                ? ($isEnum ? $parameter->getDefaultValue() : null)
                : $this->app->resolve($className);
        }

        return $skippableValue;
    }

    /**
     * Determine if an object of the given class is in a list of parameters.
     *
     * @param  string  $class
     * @param  array  $parameters
     * @return bool
     */
    protected function alreadyInParameters($class, array $parameters)
    {
        return !is_null(array_key_first(array_filter($parameters, fn($value) => $value instanceof $class)));
    }

    /**
     * Splice the given value into the parameter list.
     *
     * @param  array  $parameters
     * @param  string  $offset
     * @param  mixed  $value
     * @return void
     */
    protected function spliceIntoParameters(array &$parameters, $offset, $value)
    {
        array_splice(
            $parameters,
            $offset,
            0,
            [$value]
        );
    }
}