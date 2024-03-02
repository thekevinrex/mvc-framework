<?php

namespace App\Providers;

use app\core\bridge\BridgeWebsocketApplication;
use PhpVueBridge\Bedrock\Providers\WebsocketServiceProvider as WebsocketCoreServiceProvider;

class WebsocketServiceProvider extends WebsocketCoreServiceProvider
{

    public array $listen = [];

    public array $apps = [
        'chat' => BridgeWebsocketApplication::class,
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
?>