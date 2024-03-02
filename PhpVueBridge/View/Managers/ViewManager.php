<?php

namespace PhpVueBridge\View\Managers;

use PhpVueBridge\View\ComponentSlot;
use PhpVueBridge\View\ComponentHandler;
use PhpVueBridge\Support\Facades\Bridge;
use PhpVueBridge\View\ComponentAttributeBag;
use PhpVueBridge\Bridge\Components\VueComponentInterface;
use PhpVueBridge\Bridge\Components\BridgeComponentInterface;
use PhpVueBridge\View\Component;
use PhpVueBridge\View\ComponentResolver;

class ViewManager
{

    // The component resolver used for geting components from the container
    protected ComponentResolver $resolver;

    // The components stack being rendering
    protected array $components = [];

    // The building components stack 
    protected array $buildingComponent = [];

    // The component slots being rendering
    protected array $slots = [];

    // The building component slots 
    protected array $buildingSlots = [];

    /**
     * This load the component class and initialize the component class with the given attributes
     */
    public function component(string $componentName, string $class, string $data): Component
    {
        // Resolve the attributes in a array [ attribute name => value]
        $attributes = $this->resolveAttributes($data);

        $component = $this->getResolver()->get(
            $class,
            $attributeBag = new ComponentAttributeBag($attributes),
            ['view' => 'components/' . $componentName]
        );

        $component->withName($componentName);
        $component->withAttributes($attributeBag);

        $this->components[$componentName] = $component;

        if (method_exists($component, '_initialize')) {
            $component->_initialize($attributeBag->toArray());
        }

        return $component;
    }

    public function startComponent(string $componentName): void
    {
        if (ob_start()) {
            $this->buildingComponent[] = $componentName;
            $this->slots[$componentName] = [];
        }
    }

    /**
     * Render the component 
     */
    public function renderComponent()
    {
        // If the building component is empty is that the is no component being rendering
        if (empty($this->buildingComponent)) {
            return ob_get_clean();
        }

        // Get the component rendering
        $last = array_pop($this->buildingComponent);
        $component = $this->components[$last] ?? null;

        if (is_null($component)) {
            return ob_get_clean();
        }

        // Set the default component slot
        $this->slots[$last]['slot'] = (
            new ComponentSlot([])
        )->setContent(ob_get_clean());

        // All the component data that is being accessible inside the component
        $parameters = array_merge( // All the component data, including the public properties and public methods
            $this->slots[$last] // All the component slots
        );

        unset($this->slots[$last]);

        // Get a handler instance for the component being rendering
        $handler = Bridge::handler()->handle($component, $parameters);

        return $this->resolveRenderingComponent($handler);
    }

    protected function resolveRenderingComponent($handler)
    {
        return $handler->render();
    }

    /**
     * Load a slot and return it component for it use inside the slot template
     */
    public function slot($name, $data)
    {
        // Resolve the attributes in a array [ attribute name => value]
        $attributes = $this->resolveAttributes($data);

        // Get the component that is currently being rendered
        $lastComponent = end($this->buildingComponent);

        $this->slots[$lastComponent][$name] = new ComponentSlot($attributes);

        // Return the component that is currently being rendered
        return $this->components[$lastComponent];
    }

    // Start rendering a component slot
    public function startSlot($slot)
    {
        if (ob_start()) {
            $this->buildingSlots[] = $slot;
        }
    }

    // End rendering a component slot
    public function endSlot()
    {
        // If the building slots array is empty is that there is no rendering slot
        if (empty($this->buildingSlots)) {
            return ob_get_clean();
        }

        $lastSlot = array_pop($this->buildingSlots);
        $lastComp = end($this->buildingComponent);

        $this->slots[$lastComp][$lastSlot]->setContent(
            ob_get_clean()
        );
    }

    // Resolve the given data string name=value,name=value into a array [ name => value ]
    protected function resolveAttributes($data)
    {
        if (empty($data)) {
            return [];
        }

        $attributes = array_map(function ($attribute) {

            [$name, $value] = explode('=', $attribute, 2);

            return ['name' => $name, 'value' => $value];
        }, explode(',', $data));

        $attributesWithKey = [];
        foreach ($attributes as $attribute) {
            $attributesWithKey[$attribute['name']] = $attribute['value'];
        }

        return $attributesWithKey;
    }

    protected function getResolver(): ComponentResolver
    {
        return $this->resolver ??= new ComponentResolver;
    }
}
