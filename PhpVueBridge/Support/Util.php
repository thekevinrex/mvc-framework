<?php

namespace PhpVueBridge\Support;

use ReflectionNamedType;

class Util
{

    public static function getParameterClassName($parameter)
    {

        $type = $parameter->getType();

        if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return null;
        }

        $name = $type->getName();

        if (!is_null($class = $parameter->getDeclaringClass())) {
            if ($name === 'self') {
                return $class->getName();
            }

            if ($name === 'parent' && $parent = $class->getParentClass()) {
                return $parent->getName();
            }
        }

        return $name;
    }

    public static function StripString(string $string): string
    {
        $string = trim($string);

        if (str_starts_with($string, "'")) {
            $string = substr($string, 1);
        }

        if (str_ends_with($string, "'")) {
            $string = substr($string, 0, -1);
        }

        return $string;
    }
}
?>