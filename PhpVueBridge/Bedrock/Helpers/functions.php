<?php

use PhpVueBridge\Support\Env;
use PhpVueBridge\Support\Facades\App;
use PhpVueBridge\Support\Facades\View;
use PhpVueBridge\Support\Facades\Bridge;
use PhpVueBridge\Support\Facades\Config;
use PhpVueBridge\Bedrock\Interfaces\Htmleable;
use PhpVueBridge\Bedrock\Interfaces\Renderable;

function config($key, $default = '')
{
    return Config::get($key, $default);
}

function env($key, $default = '')
{
    return Env::get($key, $default);
}

function app($resolve = '', array $data = [])
{
    return (empty($resolve))
        ? App::resolve('app')
        : App::resolve($resolve, $data);
}

function view(string $path, array $data = [])
{
    return View::make($path, $data);
}

function asset($file)
{
    return Bridge::asset($file);
}

function e($string)
{

    if ($string instanceof Htmleable) {
        return $string->toHtml();
    }

    if ($string instanceof Renderable) {
        return $string->render();
    }

    return htmlentities($string, ENT_QUOTES, 'UTF-8');
}

function route($route = '')
{
    return app('url')->route($route);
}

function url($url = '', $params = array())
{
    if (empty($url)) {
        return app('url');
    }

    return app('url')->to($url, $params);
}


function resource_path(string $path = '')
{
    return app('app')->getResourcesPath() . (empty($path) ? '' : '/' . $path);
}

function storage_path(string $path = '')
{
    return app('app')->getStoragePath() . (empty($path) ? '' : '/' . $path);
}
