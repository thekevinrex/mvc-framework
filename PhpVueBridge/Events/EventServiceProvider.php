<?php

namespace PhpVueBridge\Events;

use PhpVueBridge\Bedrock\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bindShared('events', function ($app) {
            return (new EventManager($app));
        });
    }
}
?>