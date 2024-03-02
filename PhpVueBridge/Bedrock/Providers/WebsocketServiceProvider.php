<?php

namespace PhpVueBridge\Bedrock\Providers;

use app\Core\Websockets\ServerFactory;
use PhpVueBridge\Bedrock\Application;
use PhpVueBridge\Bedrock\ServiceProvider;


class WebsocketServiceProvider extends ServiceProvider
{

    protected ServerFactory $factory;

    public array $listen = [];

    public array $apps = [];

    public function __construct(
        Application $app
    ) {
        parent::__construct($app);
    }

    public function register(): void
    {
        # code...
    }

    public function boot(): void
    {
        $this->factory = $this->app->resolve('websocket_server');

        foreach ($this->apps as $key => $app) {
            $this->factory->registerApplication($key, $app);
        }

        foreach ($this->listen as $key => $listener) {
            $this->factory->registerListener($key, $listener);
        }
    }
}

?>