<?php


namespace app\core\view;


use app\Core\Support\ServiceProvider;
use app\core\view\Compilers\CompilerFactory;
use app\core\view\Engines\CompilerEngine;

class ViewServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bindShared('compiler', function ($app) {
            return (new CompilerFactory($app))
                ->setViewPath($this->app->getResourcesPath() . '/views/')
                ->setComponentPath($this->app->getResourcesPath() . '/views/components')
                ->setCompiledPath($this->app->getStoragePath() . '/views/');
        });

        $this->app->bindShared('engine', function ($app) {
            return new CompilerEngine($app->resolve('compiler'), $app);
        });


        $this->app->bindShared('view', function ($app) {
            return new ViewFactory($app);
        });
    }
}