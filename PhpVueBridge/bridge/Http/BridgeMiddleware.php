<?php


namespace app\core\bridge\Http;


use app\Core\Application;
use app\core\bridge\Bridge;
use app\core\facades\BridgeSeo;
use app\core\interfaces\middlewareInterface;
use app\Core\Request\Request;
use app\Core\Response\JsonResponse;
use app\Core\Response\Response;

class BridgeMiddleware implements middlewareInterface
{

    public function __construct(
        protected Application $app,
        protected Bridge $bridge,
    ) {

    }

    public function handle($request, \Closure $next)
    {

        $response = $next($request);

        if (!$response instanceof Response) {
            return $response;
        }

        if ($this->isBridgeRequest()) {
            return $this->handleBridgeResponse($response);
        } else {
            return $this->handleRegularResponse($response);
        }

    }

    protected function resolveBridgeData()
    {
        return [
            'head' => BridgeSeo::toArray(),
            'flash' => [],
            'modal' => [],
            'shared' => [],
            'errors' => [],
            'toasts' => [],
        ];
    }

    protected function isBridgeRequest(): bool
    {
        $request = app('request');

        if ($request->headers->has(Bridge::BRIDGE_HEADER)) {
            return true;
        }

        return false;
    }

    protected function handleRegularResponse(Response $response)
    {

        $bridgeData = $this->resolveBridgeData();

        $root = $this->app->resolve('view')->make('root', [
            'components' => $this->bridge->getComponents(),
            'events' => $this->bridge->handler()->getEvents(),
            'options' => $bridgeData,
            'bridgeContent' => $response->getContent(),
        ]);

        $response->setContent($root->render());

        return $response;
    }


    protected function handleBridgeResponse(Response $response)
    {
        $bridgeData = $this->resolveBridgeData();

        return (new JsonResponse([
            'html' => $response->getContent(),
            'bridge' => $bridgeData,
        ]));
    }
}