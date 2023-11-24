<?php

namespace PhpVueBridge\Routes;

use PhpVueBridge\Bedrock\Application;

class Route
{

    protected $app;

    protected $url = '';
    protected $method = 'get';
    protected $name = '';
    protected $callback;

    protected $middlewares = [];
    protected $baseName = '';
    protected $controller = '';
    protected $prefix = '';

    public function __construct(string $method, string $url)
    {
        $this->url = $url;
        $this->method = $method;
    }

    public function setContainer(Application $app)
    {
        $this->app = $app;

        return $this;
    }

    public function setGroupData(array $data)
    {
        if (isset($data['controller'])) {
            $this->controller = empty($data['controller']) ? '' : $data['controller'];
        }

        if (isset($data['middlewares'])) {
            $this->middlewares = $data['middlewares'];
        }

        if (isset($data['name'])) {
            $this->setBaseName(empty($data['name']) ? '' : $data['name']);
        }

        if (isset($data['prefix'])) {
            $this->prefix = $data['prefix'];
        }

        return $this;
    }

    /**
     * @param string $baseName
     * @return Route
     */
    public function setBaseName(string $baseName)
    {
        $this->baseName = $baseName;

        return $this;
    }

    public function setCallback($callback)
    {
        if (!$callback instanceof \Closure && is_string($this->callback)) {

            $callback = [$this->controller, $this->callback];

            if (strpos($this->callback, '@')) {
                $callback = explode($this->callback, '@', 2);
            }
        }

        $this->callback = $callback;

        return $this;
    }

    public function middleware($middlewares)
    {

        if (is_array($middlewares)) {
            foreach ($middlewares as $middleware) {
                $this->setMiddleware($middleware);
            }
        } else {
            $this->setMiddleware($middlewares);
        }

        return $this;
    }

    protected function setMiddleware($middleware)
    {
        $this->middlewares[] = $middleware;
    }

    public function getParameters()
    {
        return [];
    }

    public function getMiddlewares()
    {
        return $this->middlewares;
    }

    public function name(string $name)
    {

        $newName = (empty($this->baseName) ? '' : $this->baseName . '.') . $name;

        $this->app->resolve('router')->updateRouteName($this->name, $newName, $this->url);

        $this->name = $newName;

        return $this;
    }

    public function run()
    {

        if ($this->isControllerAction()) {
            return $this->runController();
        }

        return $this->runCallback();
    }

    protected function isControllerAction()
    {
        return !$this->callback instanceof \Closure && is_array($this->callback);
    }

    protected function runCallback()
    {
        return (new ClosureDispatcher($this->app))->dispatch($this, $this->callback);
    }

    protected function runController()
    {
        return (new ControllerResolver($this->app))
            ->resolve(
                $this,
                $this->app->resolve($this->callback[0]),
                $this->callback[1]
            );
    }

}

?>