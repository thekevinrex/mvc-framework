<?php


namespace PhpVueBridge\Events;

use PhpVueBridge\Bedrock\Application;

class EventDispatcher
{

    protected Application $app;

    protected $listener;

    public function __construct(
        Application $app,
        $listener,
    ) {
        $this->app = $app;
        $this->listener = $listener;
    }

    public function dispatch($event, array $data = [])
    {
        $listener = $this->makeListener();

        return $listener($event, $data);
    }

    protected function makeListener(): \Closure
    {
        if (is_string($this->listener)) {
            return $this->getResolverClassListener($this->listener);
        }

        if (is_array($this->listener) && isset($this->listener[0]) && is_string($this->listener[0])) {
            return $this->getResolverClassListener($this->listener[0]);
        }

        return function ($event, $data) {

            if ($this->listener instanceof \Closure) {
                return call_user_func($this->listener, ...array_values($data));
            }

            $call = $this->createClassListener($this->listener);

            return $call(...array_values($data));
        };
    }

    protected function getResolverClassListener($listener): \Closure
    {
        return function ($event, $data) use ($listener) {

            $call = $this->createClassListener($listener);

            return $call(...array_values($data));
        };
    }

    protected function createClassListener($listener): array
    {
        [$class, $method] = (is_array($listener))
            ? $listener
            : [$listener, 'handle'];


        if (!method_exists($class, $method)) {
            $method = '_invoke';
        }

        if (is_object($class)) {
            return [$class, $method];
        }

        // #TODO Handle if the handler should be queue

        $listener = $this->app->resolve($class);

        return [$listener, $method];
    }
}
?>