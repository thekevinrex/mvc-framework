<?php

namespace App\Providers;

use PhpVueBridge\Bedrock\Providers\RouteServiceProvider as RouteCoreServiceProvider;
use PhpVueBridge\Support\Facades\Route;

class RouteServiceProvider extends RouteCoreServiceProvider
{


    public function boot()
    {

        $this->routes(function () {

            Route::middleware('web')
                ->group(
                    realpath($this->app->getRoutesPath() . '/web.php')
                );

        });

    }
}
?>