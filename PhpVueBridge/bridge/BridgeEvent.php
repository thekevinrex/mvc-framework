<?php

namespace app\core\bridge;

use app\core\bridge\Components\BridgeComponentInterface;

class BridgeEvent
{

    protected BridgeComponentInterface $component;

    protected string $id;

    public function __construct(
        protected string $event,
        protected string $action,
        protected array $modifiers = []
    ) {

    }

    public function register(BridgeComponentInterface $component)
    {
        $this->component = $component;

        return ' bridge-id="' . $this->getId() . '"';
    }

    public function getId()
    {
        return $this->id ??= $this->generateEventId();
    }

    protected function generateEventId(): string
    {
        return bin2hex(random_bytes(32) . time());
    }

    public function getData(): array
    {
        return [
            'event' => $this->event,
            'action' => $this->action,
            'modifiers' => $this->modifiers,
            'component' => $this->component->getPropertiesValues(),
        ];
    }
}
?>