<?php

namespace App\Http;

use PhpVueBridge\Http\Http as HttpCore;
use PhpVueBridge\Bridge\Http\BridgeMiddleware;
use PhpVueBridge\Routes\Middlewares\HandleCors;

class Http extends HttpCore
{

    protected array $middlewares = [
        HandleCors::class
    ];

    protected array $groupMiddlewares = [
        'web' => [
            BridgeMiddleware::class
        ],
    ];

    protected array $aliasMiddlewares = [

    ];

}

?>