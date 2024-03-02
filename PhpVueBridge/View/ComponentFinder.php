<?php

namespace PhpVueBridge\View;

class ComponentFinder
{

    public static string $namespace = '\\App\\View\\';

    public static string $componentNamespace = '\\App\\View\\Components\\';

    public static function getClassResolver(string $view)
    {
        return self::findClassResolver(
            self::formatClassName($view),
            self::$namespace,
        );
    }

    public static function getComponentResolver(string $component)
    {
        return self::findClassResolver(
            self::formatClassName($component),
            self::$componentNamespace,
        );
    }

    public static function findClassResolver(string $class, string $namespace)
    {
        if (class_exists($namespace . $class)) {
            return $namespace . $class;
        }

        if (class_exists($namespace . $class . 'Component')) {
            return $namespace . $class . 'Component';
        }

        if (str_starts_with($class, $namespace)) {
            if (class_exists($class)) {
                return $class;
            }
        }

        $class = str_replace('\\', '.', $class);

        if (!class_exists($class)) {
            return AnonymosComponent::class;
        }

        return $class;
    }

    public static function formatClassName(string $component)
    {
        $paths = array_map(function ($path) {
            if (count($names = explode('-', $path)) > 1) {
                return array_reduce($names, function ($cary, $name) {
                    return $cary . ucfirst($name);
                }, '');
            } else {
                return ucfirst($path);
            }
        }, explode('.', $component));

        return implode('\\', $paths);
    }
}
