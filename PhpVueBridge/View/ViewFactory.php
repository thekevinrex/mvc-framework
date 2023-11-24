<?php


namespace app\core\view;


use app\Core\Application;

class ViewFactory
{

    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function make($path, $data = [])
    {
        return new View(
            $this->app,
            $this->app->resolve('engine'),
            $path,
            $data
        );
    }
}