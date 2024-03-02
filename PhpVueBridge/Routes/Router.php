<?php

namespace PhpVueBridge\Routes;

use PhpVueBridge\Bedrock\Application;
use PhpVueBridge\Http\Request\Request;
use PhpVueBridge\Http\Response\Response;
use PhpVueBridge\Routes\Middlewares\CheckLine;
use PhpVueBridge\Routes\Contracts\RouterContract;
use PhpVueBridge\Routes\Exceptions\RouteNotFoundException;

class Router implements RouterContract
{

    protected Application $app;

    protected Route $currentRoute;

    protected Request $request;

    protected array $groups = [];

    protected string $prefix;

    protected array $middlewares = [];

    protected string $name;

    protected string $controller;

    protected $middlewaresGroup = [];

    protected $middlewaresAlias = [];

    protected $compiledRoutes = false;

    protected $routes = [
        'post' => [],
        'get' => [],
    ];

    protected $namedRoutes = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    // Register al the routes types [GET, POST, PUT, DELETE]

    public function get(string $url, $callback)
    {
        return $this->addNewRoute('get', $url, $callback);
    }

    // All the group info settings [PREFIX, MIDDLEWARE, CONTROLLER, NAME, GROUP]

    public function prefix(string $prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function middleware($middleware)
    {
        if (!is_array($middleware))
            $middleware = [$middleware];

        $this->middlewares = array_merge($this->middlewares, $middleware);

        return $this;
    }

    public function controller($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    public function group($callback)
    {

        $lastGroup = $this->getLastGroup();

        $group = [
            'name' => $lastGroup['name'] . ($this->name ?? ''),
            'prefix' => $lastGroup['prefix'] . ($this->prefix ?? ''),
            'middlewares' => array_merge($lastGroup['middlewares'], ($this->middlewares ?? [])),
            'controller' => $this->controller ?? ''
        ];

        array_push($this->groups, $group);

        if (!$callback instanceof \Closure) {
            $callback = $this->registerRoutesFromFile($callback);
        }

        $callback();

        array_pop($this->groups);

        $this->cleanGroupInfo();
    }

    protected function cleanGroupInfo()
    {
        $this->name = '';
        $this->controller = '';
        $this->middlewares = [];
        $this->prefix = '';
    }

    public function getLastGroup()
    {
        return (empty($this->groups))
            ? ['prefix' => '', 'middlewares' => [], 'name' => '', 'controller' => '']
            : end($this->groups);
    }

    protected function registerRoutesFromFile($file)
    {
        return function () use ($file) {
            if (!file_exists($file)) {
                return;
            }

            return require_once $file;
        };
    }

    protected function addNewRoute(string $method, string $url, $callback)
    {

        $lastGroup = $this->getLastGroup();

        $url = ($lastGroup['prefix'] ?? '') . $url;

        $route = $this->routes[$method][$url] = new Route($method, $url);

        $route->setContainer($this->app)
            ->setGroupData($lastGroup)
            ->setCallback($callback);

        return $route;
    }

    public function updateRouteName($oldName, $newName, $url)
    {

        if (!empty($oldName)) {
            unset($this->namedRoutes[$oldName]);
        }

        $this->namedRoutes[$newName] = $url;
    }

    public function resolve(Request $request)
    {
        $this->request = $request;

        return $this->runRoute($request, $this->findRoute($request));
    }

    protected function findRoute(Request $request): Route
    {
        if (!$this->compiledRoutes) {
            $this->app->callMethodBinding('compileRoutes');
            $this->compiledRoutes = true;
        }

        $route = $this->routes[strtolower($request->getMethod())][$request->getPath()] ?? false;

        if (!$route) {
            throw new RouteNotFoundException('http not found');
        }

        $this->app->instance(Route::class, $route);

        return $route;
    }

    public function getCurrentRoute()
    {
        return $this->currentRoute;
    }

    public function prepareResponse(Request $request, $response)
    {
        return (new Response($response))->prepare($request);
    }

    public function runRoute(Request $request, Route $route)
    {

        return (new CheckLine($this->app))
            ->check($route)
            ->throw(
                $this->getRouteMiddleware($route)
            )
            ->then(
                function (Route $route) use ($request) {
                    return $this->prepareResponse(
                        $request,
                        $route->run(),
                    );
                }
            );
    }

    protected function getRouteMiddleware(Route $route)
    {
        $middlewares = $route->getMiddlewares();

        $routeMiddleware = [];

        foreach ($middlewares as $middleware) {

            $resolvedMiddlewares = $this->resolveMiddlewareName($middleware);

            if (is_array($resolvedMiddlewares)) {
                $routeMiddleware = array_merge($routeMiddleware, $resolvedMiddlewares);
            } else if (!is_null($resolvedMiddlewares)) {
                $routeMiddleware[] = $resolvedMiddlewares;
            }

        }

        return $routeMiddleware;
    }

    protected function resolveMiddlewareName($middleware)
    {

        if ($middleware instanceof \Closure) {
            return $middleware;
        }

        if (isset($this->middlewaresAlias[$middleware]) && $this->middlewaresAlias[$middleware] instanceof \Closure) {
            return $this->middlewaresAlias[$middleware];
        }

        if (isset($this->middlewaresGroup[$middleware])) {
            return $this->middlewaresGroup[$middleware];
        }

        [$name, $parameters] = array_pad(explode(':', $middleware, 2), 2, null);

        $middleware = ($this->middlewaresAlias[$name] ?? $name);

        if (!class_exists($middleware))
            return null;

        return $middleware . (!is_null($parameters) ? ':' . $parameters : '');
    }

    public function setGroupMiddleware($middlewares)
    {
        $this->middlewaresGroup = array_merge($this->middlewaresGroup, $middlewares);
    }

    public function setAliasesMiddleware($middlewares)
    {
        $this->middlewaresAlias = array_merge($this->middlewaresAlias, $middlewares);
    }

    public function renderError($response)
    {
        $response->send();
    }

    public function getRouteByName($name)
    {

        if (!$this->compiledRoutes) {
            $this->app->callMethodBinding('compileRoutes');
            $this->compiledRoutes = true;
        }
        return $this->namedRoutes[$name] ?? null;
    }
}
?>