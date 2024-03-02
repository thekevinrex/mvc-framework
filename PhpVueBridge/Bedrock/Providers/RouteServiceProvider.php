<?php

namespace PhpVueBridge\Bedrock\Providers;

use PhpVueBridge\Bedrock\Application;
use PhpVueBridge\Bedrock\ServiceProvider;
use PhpVueBridge\Routes\Router;

class RouteServiceProvider extends ServiceProvider
{
    protected Router $router;

    public function __construct(Application $app)
    {
        $this->router = $app->resolve('router');

        parent::__construct($app);
    }

    public function register(): void
    {

    }

    public function routes($callback)
    {
        $this->app->bindMethod('compileRoutes', function () use ($callback) {
            return $callback();
        });
    }

}