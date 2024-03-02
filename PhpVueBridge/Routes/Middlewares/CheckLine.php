<?php


namespace PhpVueBridge\Routes\Middlewares;

use PhpVueBridge\Bedrock\Application;

class CheckLine
{

    protected Application $app;

    protected array $throw = [];

    protected $subject;

    protected string $method = 'handle';

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function throw (array $throw)
    {
        $this->throw = $throw;

        return $this;
    }

    public function check($check)
    {
        $this->subject = $check;

        return $this;
    }

    public function then($callback)
    {
        return $this->runLine($this->prepareCallback($callback));
    }

    public function prepareCallback($callback): \Closure
    {
        return function ($line) use ($callback) {
            try {
                return $callback($line);
            } catch (\Throwable $e) {
                $this->handleException($e);
            }
        };
    }

    protected function runLine($callback)
    {

        foreach (array_reverse($this->throw) as $checker) {
            $callback = function ($line) use ($checker, $callback) {
                try {

                    if (is_string($checker)) {
                        $checker = $this->app->resolve($checker);
                    }

                    return (is_object($checker)) ? $checker->handle($line, $callback) : $checker($line, $callback);
                } catch (\Throwable $e) {
                    $this->handleException($e);
                }
            };
        }

        return $callback($this->subject);
    }

    public function handleException(\Throwable $e)
    {
        throw $e;
    }
}