<?php

namespace app\core\bridge\Components;

use app\core\view\Component;

abstract class BridgeComponent extends Component implements BridgeComponentInterface
{

    protected string $view;

    protected string $id;

    public function __construct($view)
    {
        $this->view = $view;
        $this->generateComponentId();
    }

    protected function generateComponentId(): void
    {
        $this->id = 4452;
    }

    public function render()
    {
        return view($this->view);
    }

    protected function getId(): string
    {
        return $this->id ??= bin2hex(random_bytes(32) . time());
    }

    public function isVueComponent(): bool
    {
        return str_ends_with(app('compiler')->viewPath($this->view), '.vue.phtml');
    }
}
?>