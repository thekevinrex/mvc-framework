<?php


namespace PhpVueBridge\Support\Facades;


class Route extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'router';
    }
}