<?php

namespace PhpVueBridge\Support\Facades;

use PhpVueBridge\Bedrock\Application;

abstract class Facade
{

    protected static $app = null;

    protected static $resolvedInstance = [];

    protected static $cached = true;

    public static function setApplication(Application $app)
    {
        static::$app = $app;
    }

    public function getApplication()
    {
        return static::$app;
    }

    public static function clearResolvedIntancies()
    {
        static::$resolvedInstance = [];
    }

    protected static function getFacadeRoot()
    {
        return static::resolveFacadeInstance(static::getFacadeAccessor());
    }

    protected static function getFacadeAccessor()
    {
        return null;
    }

    protected static function resolveFacadeInstance($accessor)
    {

        if (is_null($accessor))
            return null;

        if (isset(static::$resolvedInstance[$accessor]))
            return static::$resolvedInstance[$accessor];

        if (static::$app) {
            if (static::$cached) {
                static::$resolvedInstance[$accessor] = static::$app->resolve($accessor);
            }

            return static::$app->resolve($accessor);
        }

        return null;
    }

    public static function __callStatic($method, $arg)
    {

        $instance = static::getFacadeRoot();

        if (is_null($instance)) {
            return null;
        }

        return $instance->$method(...$arg);
    }

}
?>