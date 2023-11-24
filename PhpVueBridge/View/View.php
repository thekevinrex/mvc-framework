<?php


namespace app\core\view;


use app\Core\Application;
use app\core\contracts\ViewContract;
use app\core\view\Compilers\Compiler;
use app\core\view\Engines\Engine;

class View implements ViewContract
{

    protected Application $app;

    protected Engine $engine;

    protected string $view;

    protected array $data = [];

    public function __construct(
        Application $app,
        Engine $engine,
        string $view,
        array $data = []
    ) {
        $this->app = $app;
        $this->engine = $engine;
        $this->data = $data;
        $this->view = $view;
    }

    public function render(array $data = [])
    {
        return $this->engine->render($this->view, array_merge($this->data, $data));
    }

    public function getEngine(): Engine
    {
        return $this->engine;
    }

    public function getView(): string
    {
        return $this->view;
    }
}