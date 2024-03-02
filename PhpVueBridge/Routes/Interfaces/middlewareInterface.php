<?php


namespace PhpVueBridge\Routes\Interfaces;


interface MiddlewareInterface
{

    public function handle(any $request, \Closure $next);

}