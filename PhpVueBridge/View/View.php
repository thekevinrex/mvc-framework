<?php


namespace PhpVueBridge\View;

use PhpVueBridge\View\Contracts\ViewContract;
use PhpVueBridge\view\Engines\Engine;
use PhpVueBridge\View\Interfaces\ViewInterface;


class View implements ViewInterface
{

    protected array $data = [];

    protected Component $component;

    public function __construct(
        protected ViewContract $factory,
        protected Engine $engine,
        protected string $path,
        protected string $view,
        array $data = []
    ) {
        $this->data = $this->parseData($data);
    }

    public function render()
    {
        try {

            $content = $this->renderContent();
        } catch (\Exception $e) {
            throw $e;
        }

        return $content;
    }

    protected function renderContent()
    {

        $this->factory->startRendering($this->view);

        $content = $this->getContent();

        $this->factory->endRendering();

        return $content;
    }

    protected function getContent()
    {
        return $this->getEngine()->get($this->path, $this->getData());
    }

    public function with(string|array $key, $value = null): self
    {
        $key = is_array($key) ? $key : [$key => $value];

        $this->data = array_merge($this->data, $this->parseData($key));

        return $this;
    }

    public function getEngine(): Engine
    {
        return $this->engine;
    }

    public function getView(): string
    {
        return $this->view;
    }

    public function withComponent(Component $component): self
    {
        $this->component = $component;

        return $this;
    }

    public function getComponent()
    {
        return $this->component ??= $this->findComponent();
    }

    protected function parseData(array $data): array
    {
        return $data;
    }

    protected function getData(): array
    {
        return array_merge(
            $this->factory->getShared(),
            $this->getComponent()->_getData(),
            $this->data,
        );
    }

    public function __toString()
    {
        return $this->render();
    }

    public function toHtml(): string
    {
        return $this->render();
    }

    protected function findComponent(): Component
    {
        $component = app(
            ComponentFinder::getClassResolver($this->view),
            ['view' => $this->view]
        );

        $component->withName($this->view);

        return $component;
    }
}
