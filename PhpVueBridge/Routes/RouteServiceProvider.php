<?php

namespace PhpVueBridge\Routes;

use PhpVueBridge\Bedrock\Application;
use PhpVueBridge\Routes\Router;
use PhpVueBridge\Bedrock\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bindShared('router', function (Application $app) {
            return new Router($app);
        });

        $this->app->bindShared('url', function (Application $app) {
            return new UrlGenerator($app->resolve('router'), $app->resolve('request'));
        });
    }

}