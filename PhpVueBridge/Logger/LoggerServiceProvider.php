<?php

namespace PhpVueBridge\Logger;

use PhpVueBridge\Bedrock\ServiceProvider;

class LoggerServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind('logger', function ($app) {
            return new StdOutLogger();
        });
    }
}

?>