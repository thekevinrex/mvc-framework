<?php

namespace App\Http;

use app\core\bridge\Http\BridgeMiddleware;
use PhpVueBridge\Http\Http as HttpCore;

class Http extends HttpCore
{

    protected array $middlewares = [

    ];

    protected array $groupMiddlewares = [
        // 'web' => [
        //     BridgeMiddleware::class
        // ],
    ];

    protected array $aliasMiddlewares = [

    ];

}

?>