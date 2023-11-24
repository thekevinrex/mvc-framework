<?php

namespace app\Core\view\Engines;

use app\Core\Application;
use app\core\facades\Bridge;
use app\core\view\Compilers\CompilerFactory;
use app\core\view\ComponentAttributeBag;
use app\core\view\ComponentHandler;
use app\core\view\ComponentSlot;
use app\core\view\Engines\Managers\SectionsManager;

class ViewEngine
{

    use SectionsManager;

    protected array $components = [];

    protected array $buildingComponent = [];

    protected array $slots = [];

    protected CompilerEngine $engine;


    public function __construct(CompilerEngine $engine)
    {
        $this->engine = $engine;
    }

    public function render(string $view, array $data = [])
    {
        return $this->engine->render($view, $data);
    }

    public function component($componentName, $class, $data)
    {
        $attributes = $this->resolveAttributes($data);

        $component = app($class, [
            'view' => 'components/' . $componentName,
        ]);

        $this->components[$componentName] = [
            'name' => $componentName,
            'component' => $component,
            'attributes' => new ComponentAttributeBag($attributes),
            'slots' => [],
        ];

        if (method_exists($component, '_initialize')) {
            $component->_initialize($attributes);
        }

        return $component;
    }

    public function startComponent($componentName)
    {
        if (ob_start())
            $this->buildingComponent[] = $componentName;
    }

    public function renderComponent()
    {
        if (empty($this->buildingComponent)) {
            return ob_get_clean();
        }

        $last = array_pop($this->buildingComponent);

        $componentData = $this->components[$last] ?? [];

        if (empty($componentData)) {
            return ob_get_clean();
        }

        $componentData['slots']['slot_slot'] = (new ComponentSlot('slot', []))->setContent(ob_get_clean());

        return $this->resolveRenderingComponent($componentData);
    }

    protected function resolveRenderingComponent($componentData)
    {

        $isVue = false;
        if ($this->isVueComponent($componentData['component'])) {
            $isVue = true;
        }

        $handler = new ComponentHandler($componentData['component']);

        if ($this->isBridgeComponent($componentData['component']) || $isVue) {
            $handler = Bridge::handler()->handle($componentData);
        }

        $parameters = [
            'attributes' => $componentData['attributes'],
            ...$componentData['component']->getPropertiesValues(),
            ...$this->parseComponentSlots($componentData['slots'])
        ];

        if ($isVue) {

            Bridge::registerVueComponent(
                $componentData,
                $handler->render($parameters)
            );

            return implode(PHP_EOL, [
                "<" . ucfirst($componentData['name']) . ' ' . $componentData['attributes'] . ">",
                $componentData['slot_slot'] ?? '',
                "</" . ucfirst($componentData['name']) . ">",
            ]);
        } else
            return $handler->render($parameters)[0];
    }

    protected function isVueComponent($component): bool
    {
        if ($component instanceof \app\Core\bridge\Components\VueComponentInterface) {
            return $component->isVueComponent();
        }

        return method_exists($component, 'isVueComponent') && $component->isVueComponent();
    }

    protected function isBridgeComponent($component): bool
    {
        return $component instanceof \app\core\bridge\Components\BridgeComponentInterface;
    }

    protected function parseComponentSlots($slots): array
    {
        $componentSlots = array();

        foreach (array_filter(array_keys($slots), function ($key) {
            return str_starts_with($key, 'slot_');
        }) as $slot) {

            $slot_name = substr($slot, 5);

            if (count($names = explode('-', $slot_name)) > 1) {
                $name = array_reduce($names, function ($cary, $name) {
                    return $cary . ucfirst($name);
                }, '');
            } else {
                $name = $slot_name;
            }

            $componentSlots[lcfirst($name)] = $slots[$slot];
        }

        return $componentSlots;
    }

    protected function attributesToString($attributes)
    {
        $attributesString = [];
        foreach ($attributes as $key => $attribute) {
            $attributesString[] = "{$key}='{$attribute}'";
        }

        return implode(' ', $attributesString);
    }

    public function slot($name, $data)
    {
        $attributes = $this->resolveAttributes($data);
        $lastComponent = end($this->buildingComponent);

        $this->components[$lastComponent]['slots']["slot_{$name}"] = new ComponentSlot($name, $attributes);

        return $this->components[$lastComponent]['component'];
    }

    public function startSlot($slot)
    {
        if (ob_start())
            $this->slots[] = 'slot_' . $slot;
    }

    public function endSlot()
    {
        if (empty($this->slots))
            return '';

        $lastSlot = array_pop($this->slots);
        $lastComp = end($this->buildingComponent);

        $this->components[$lastComp]['slots'][$lastSlot]->setContent(ob_get_clean());
    }

    protected function resolveAttributes($data)
    {

        if (empty($data)) {
            return [];
        }

        $attributes = array_map(function ($attribute) {

            [$name, $value] = explode('=', $attribute);

            return ['name' => $name, 'value' => $value];
        }, explode(',', $data));

        $attributesWithKey = [];
        foreach ($attributes as $attribute) {
            $attributesWithKey[$attribute['name']] = $attribute['value'];
        }

        return $attributesWithKey;
    }
}
?>