<?php

namespace PhpVueBridge\Routes\Middlewares;

use PhpVueBridge\Bedrock\Application;
use PhpVueBridge\Routes\Cors\CorsHandler;
use PhpVueBridge\Routes\Interfaces\MiddlewareInterface;

class HandleCors implements MiddlewareInterface
{

    public function __construct(
        protected Application $app,
        protected CorsHandler $handler
    ) {

    }

    public function handle($request, \Closure $next)
    {

        if ($this->handler->isPreflightRequest($request)) {

            $response = $this->handler->handlePreflightRequest($request);

            return $response;
        }

        $response = $next($request);

        if ($request->getMethod() === 'OPTIONS') {
            $this->handler->varyHeader($response, 'Access-Control-Request-Method');
        }

        return $this->handler->addActualRequestHeaders($response, $request);
    }
}

?>