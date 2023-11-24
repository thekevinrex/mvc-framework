<?php

namespace app\core\view;

use app\core\view\Engines\CompilerEngine;

class ComponentHandler
{

    protected Component $component;

    public function __construct($component)
    {
        $this->component = $component;
    }

    public function render(array $parameters = [])
    {
        return app('engine')->renderComponent($this->component, $parameters);
    }

    public function getComponent(): Component
    {
        return $this->component;
    }
}
?>