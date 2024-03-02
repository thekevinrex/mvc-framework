<?php


namespace PhpVueBridge\View\Engines;


abstract class Engine
{
    public abstract function get(string $file, array $data = []): string;
}
