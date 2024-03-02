<?php

namespace PhpVueBridge\View\Compilers\Managers;

use PhpVueBridge\Support\Util;

trait compileComponent
{

    protected array $components = [];

    public function compileComponent($node, $arguments)
    {
        [$tagName, $class] = $arguments;

        $attributes = '[]';
        if (count($arguments) > 2) {
            $attributes = $arguments[2];
        }

        $this->components[] = $hash = sha1('component_' . $tagName);

        return $node->textNode(
            [
                "<?php if (isset(\$component)){ \$component_{$hash} = \$component; } ?>",
                "<?php \$component = \$_engine->component('{$tagName}', '{$class}','{$attributes}'); ?>",
                "<?php if(isset(\$component) && \$component->shouldRender()): ?>",
                "<?php \$_engine->startComponent('{$tagName}'); ?>",
            ]
        );
    }

    public function compileEndComponent($node)
    {
        $hash = array_pop($this->components);

        return $node->textNode([
            "<?php echo \$_engine->renderComponent(); endif; ?>",
            "<?php if (isset(\$_component_{$hash})){ \$_component = \$_component_{$hash}; } ?>",
        ]);
    }

    public function slot($directive)
    {
        [$name, $attributes] = array_map('trim', explode(",", $directive[2], 2));

        $name = trim($name);

        return implode("\n", [
            "<?php \${$name} = \$_engine->slot('{$name}','{$attributes}'); ?>",
            "<?php \$_engine->startSlot('{$name}') ?>",
        ]);
    }

    public function endSlot()
    {
        return "<?php \$_engine->endSlot() ?>";
    }

    public function props($matches)
    {
        $propsString = $matches[2];
        if (str_starts_with($matches[2], '[') && str_ends_with($matches[2], ']')) {
            $propsString = substr($matches[2], 1, -1);
        }

        $props = [];
        foreach (explode(',', $propsString) as $prop) {
            $prop = Util::StripString($prop);
            $props[] = "<?php \${$prop} = \$attributes->prop('{$prop}', null) ?>";
        }

        return implode("\n", $props);
    }
}
