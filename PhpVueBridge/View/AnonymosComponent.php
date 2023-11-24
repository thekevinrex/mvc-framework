<?php


namespace app\core\view;


use app\Core\Application;

class AnonymosComponent extends Component
{

    protected string $view;

    protected Application $app;

    public function __construct(Application $app, $view)
    {
        $this->view = $view;
        $this->app = $app;
    }

    public function render()
    {
        return $this->app->resolve('view')->make($this->view);
    }

    public function isVueComponent(): bool
    {
        return str_ends_with($this->app->resolve('compiler')->viewPath($this->view), '.vue.phtml');
    }

}