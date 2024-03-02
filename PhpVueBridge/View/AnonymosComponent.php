<?php


namespace PhpVueBridge\View;

use PhpVueBridge\View\View;


class AnonymosComponent extends Component
{

    protected string $view;

    public function __construct($view)
    {
        $this->view = $view;
    }

    public function render(): string|View
    {
        return view($this->view);
    }
}
