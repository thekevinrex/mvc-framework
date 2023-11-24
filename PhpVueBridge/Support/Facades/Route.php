<?php


namespace PhpVueBridge\Support\facades;


class Route extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'router';
    }
}