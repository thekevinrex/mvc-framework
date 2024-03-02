<?php

namespace PhpVueBridge\View;

class RenderlessComponent extends Component
{
    public function render(): string|View
    {
        return view($this->viewName);
    }
}
