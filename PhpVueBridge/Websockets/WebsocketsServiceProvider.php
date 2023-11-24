<?php

namespace app\Core\Websockets;

use app\Core\Support\ServiceProvider;

class WebsocketsServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bindShared('websocket_server', function ($app) {
            return new ServerFactory(
                $app->resolve('logger'),
            );
        });

        $this->app->bind('websocket_client', function ($app) {
            return new Client(
                config('websocket.server'),
                config('app.url')
            );
        });
    }

    public function boot(): void
    {

    }
}

?>