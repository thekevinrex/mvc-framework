<?php

namespace PhpVueBridge\Http;

use PhpVueBridge\Bedrock\Application;
use PhpVueBridge\Bedrock\Exceptions\BeforeHandlingError;
use PhpVueBridge\Http\Contracts\HttpContract;
use PhpVueBridge\Http\Request\Request;
use PhpVueBridge\Http\Response\Response;
use PhpVueBridge\Routes\Router;

/**
 * Class that is in charge of handling all the http request to the app
 */
class Http implements HttpContract
{

    protected array $bootstrapers = [];

    protected array $middlewares = [];

    protected array $groupMiddlewares = [];

    protected array $aliasMiddlewares = [];

    protected int $requestStartedAt;

    public function __construct(
        protected Application $app,
        protected Router $router
    ) {
        $this->app = $app;
        $this->router = $router;

        $this->sendMiddlewareToRouter();
    }

    /**
     * It handle the incoming request and return the proper response
     * Also it bootstrap the app
     * and it pass the request to the router to check the routes
     */
    public function handleIncomingRequest(Request $request): Response
    {

        $this->requestStartedAt = microtime(true);

        try {

            $response = $this->sendRequestToRouter($request);

        } catch (\Throwable $e) {
            $response = $this->app->resolve(
                \PhpVueBridge\Bedrock\Contracts\HandlerContract::class
            )->response($e);
        }

        return $response;
    }

    protected function sendRequestToRouter(Request $request): Response
    {

        // Instance the request for his use later
        $this->app->instance('request', $request);

        // Bootstrap the app with the given bootstrapper
        $this->app->bootstrap(
            array_merge([
                \PhpVueBridge\Bedrock\Bootstrapers\LoadEnvironmentVariables::class,
                \PhpVueBridge\Bedrock\Bootstrapers\LoadConfigFiles::class,
                \PhpVueBridge\Bedrock\Bootstrapers\HandleExceptions::class,
                \PhpVueBridge\Bedrock\Bootstrapers\RegisterFacades::class,
                \PhpVueBridge\Bedrock\Bootstrapers\RegisterProviders::class,
            ], $this->bootstrapers)
        );

        return
            (new \PhpVueBridge\Routes\Middlewares\CheckLine($this->app))
                ->throw($this->middlewares)
                ->check($request)
                ->then(function ($request) {

                    $this->app->instance('request', $request);

                    return $this->router->resolve(
                        $request
                    );
                });
    }

    public function terminate()
    {
        //TODO terminate the middlewares call the terminate method in each middleware
    }

    protected function sendMiddlewareToRouter()
    {
        $this->router->setAliasesMiddleware($this->aliasMiddlewares);
        $this->router->setGroupMiddleware($this->groupMiddlewares);
    }
}
?>