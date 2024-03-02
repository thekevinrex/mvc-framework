<?php
use App\Providers\RouteServiceProvider;
use App\Providers\WebsocketServiceProvider;
use PhpVueBridge\Bedrock\ServiceProvider;

return [

    'name' => 'mvc-framework',

    'url' => 'http://localhost:8000',

    'debug' => true,

    'env' => env('APP_ENV', 'production'),
    'DB' => env('DB_CONNECTION', 'mysql'),

    'providers' => ServiceProvider::getDefaultProviders()->merge([
        RouteServiceProvider::class,
        // WebsocketServiceProvider::class,
    ]),
];
?>