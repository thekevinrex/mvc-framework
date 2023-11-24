<?php
use app\core\bridge\BridgeWebsocketApplication;

return [

    'server' => 'ws://localhost:8080',

    'allowed_host' => [
        'localhost',
        '127.0.0.1',
    ],

    'origin_limiter' => true,

    'origin_limiter_options' => [
        'allowed_origin' => []
    ],

    'rate_limiter' => true,

    'rate_limiter_options' => [
        'max_connections' => 100,
        'max_connections_per_ip' => 5,
        'requests_per_minute' => 10
    ],


];
?>