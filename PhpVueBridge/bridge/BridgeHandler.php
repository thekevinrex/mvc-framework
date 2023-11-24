<?php


namespace app\core\bridge;

use app\core\bridge\Components\BridgeComponentInterface;
use app\core\view\ComponentHandler;
use app\core\view\View;

class BridgeHandler
{

    const AVIABLE_EVENTS = ['click', 'model'];

    protected array $components = [];

    protected array $events = [];

    protected BridgeComponentInterface $component;

    public function __construct()
    {

    }

    public function handle(array $componentData)
    {
        $this->components[] = new ComponentHandler($componentData['component']);

        return $this;
    }

    // public function onMount()
    // {
    //     if (method_exists($this->component, 'mount')) {
    //         $this->component->mount();
    //     }
    // }

    // public function onRequest()
    // {
    //     # code...
    // }


    public function render(array $parameters = [])
    {

        if (empty($this->components)) {
            return '';
        }

        [$content, $engine] = end($this->components)->render($parameters);

        $content = $this->compileBridgeTags($content);

        return [$content, $engine];
    }

    protected function compileBridgeTags($view)
    {
        return preg_replace_callback('/
            \s*bridge:([\w\-\:\.]*)
            =(
                \"[^\"]+\"
                |
                \\\'[^\\\']+\\\'
                |
                [^\s>]+
            )\s*/x', function ($matches) {
            return $this->handleBridgeDirective($matches);
        }, $view);
    }

    protected function handleBridgeDirective(array $matches = []): string
    {

        if (count($matches) < 3) {
            return '';
        }

        [$commands, $value] = [
            explode('.', $matches[1]),
            substr($matches[2], 1, -1)
        ];

        $event = array_shift($commands);
        $action = $this->validateDirectiveValue($value, $event);

        if (!$action || !in_array($event, self::AVIABLE_EVENTS)) {
            return '';
        }

        return $this->registerBridgeEvent($event, $action, $commands);
    }

    protected function registerBridgeEvent(string $event, string $action, array $modifiers = []): string
    {
        $component = end($this->components);

        $this->events[] = $event = new BridgeEvent($event, $action, $modifiers);

        return $event->register($component);
    }

    protected function validateDirectiveValue(string $value, string $event)
    {
        if (!$value || empty($this->components)) {
            return false;
        }

        $component = end($this->components);

        if (method_exists($component, $value)) {
            if ((new \ReflectionMethod($component, $value))->isPublic()) {
                return $value;
            }
        }

        if (in_array($value, array_keys($component->getComponent()->getProperties()))) {
            if (in_array($event, ['model'])) {
                return $value;
            }
        }

        return false;
    }

    public function getEvents(): array
    {
        $events = [];

        foreach ($this->getRawEvents() as $event) {
            $events[$event->getId()] = $event->getData();
        }

        return $events;
    }

    public function getRawEvents(): array
    {
        return $this->events;
    }
}
?>