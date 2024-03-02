<?php

namespace PhpVueBridge\Bridge\Components;

use PhpVueBridge\Bedrock\Interfaces\Htmleable;
use PhpVueBridge\View\View;
use PhpVueBridge\View\Component;

class ComponentHandler
{

    public function __construct(
        protected Component $component,
        protected array $parameters = []
    ) {
    }

    public function render()
    {
        $view = $this->component->resolveView();

        if ($view instanceof View) {

            $view->withComponent(
                $this->component
            );

            return $view->with($this->parameters)->render();
        } else if ($view instanceof Htmleable) {
            return $view->toHtml();
        } else {
            return view($view, $this->parameters)
                ->withComponent($this->component);
        }
    }

    public function getComponent()
    {
        return $this->component;
    }
}
