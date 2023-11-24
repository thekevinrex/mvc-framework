<?php


namespace app\core\view\Compilers;

use app\core\view\AnonymosComponent;

class ComponentTagCompiler
{

    protected string $namespace;

    protected array $building = [];

    public function __construct($namespace)
    {
        $this->namespace = $namespace;
    }

    public function compile(string $content)
    {

        $content = $this->compileSlots($content);
        $content = $this->compileComponentSelClosingTag($content);
        $content = $this->compileComponentCloseTag($content);
        $content = $this->compileComponentTag($content);

        return $content;
    }

    protected function handleComponent($component, $attributes = []): string
    {
        $class = $this->getComponentResolver($component);

        if (!class_exists($class)) {
            $class = AnonymosComponent::class;
        }

        return $this->compileStartTag(
            $component,
            $class,
            $attributes
        );
    }

    protected function compileStartTag($name, $class, $attributes)
    {

        $hash = sha1('component' . $name);

        return implode("\n", [
            "<?php \$component = \$_engine->component('{$name}', '{$class}'," . '"' . $this->attributesToString($attributes) . '"' . "); ?>",
            "<?php if (isset(\$component)){ \$component_{$hash} = \$component; } ?>",
            "<?php if(isset(\$component_{$hash}) && \$component_{$hash}->shouldRender()): ?>",
            "<?php \$_engine->startComponent('{$name}'); ?>",
        ]);
    }

    protected function closeTag()
    {
        return PHP_EOL . "<?php echo \$_engine->renderComponent(); endif; ?>";
    }

    protected function attributesToString($attributes)
    {
        $attributesString = [];
        foreach ($attributes as $key => $attribute) {
            $attributesString[] = "{$key}={$attribute}";
        }

        return implode(',', $attributesString);
    }

    protected function getComponentResolver($component)
    {
        $class = $this->formatClassName($component);

        if (class_exists($this->namespace . $class . 'Component')) {
            return $this->namespace . $class . 'Component';
        }

        if (str_starts_with($component, $this->namespace)) {
            if (class_exists($component)) {
                return $component;
            }
        }

        return str_replace('\\', '.', $class);
    }

    protected function formatClassName($component)
    {
        $paths = array_map(function ($path) {
            if (count($names = explode('-', $path)) > 1) {
                return array_reduce($names, function ($cary, $name) {
                    return $cary . ucfirst($name);
                }, '');
            } else {
                return ucfirst($path);
            }
        }, explode('.', $component));

        return implode('\\', $paths);
    }


    protected function parseShortAttributeSyntax(string $value)
    {
        return preg_replace_callback("/\s\:\\\$(\w+)/x", function (array $matches) {
            return " :{$matches[1]}=\"\${$matches[1]}\"";
        }, $value);
    }

    protected function getAttributesFromString($string)
    {
        $string = $this->parseShortAttributeSyntax($string);

        if (
            !preg_match_all('/
            (?<attribute>[\w\-:.@]+)
            (
                =
                (?<value>
                    (
                        \"[^\"]+\"
                        |
                        \\\'[^\\\']+\\\'
                        |
                        [^\s>]+
                    )
                )
            )?
        /x', $string, $matches, PREG_SET_ORDER)
        ) {
            return [];
        }
        $attributes = [];

        foreach ($matches as $match) {

            if (!isset($match['value']) || !isset($match['attribute'])) {
                continue;
            }

            $attributeName =
                (str_starts_with($match["attribute"], '::'))
                ? substr($match["attribute"], 1)
                : $match["attribute"];

            $value = substr($match["value"], 1, -1);

            if (!str_starts_with($attributeName, ':')) {
                if (preg_match('/(@)?{{\s*(.+?)\s*}}(\r?\n)?/s', $value) && !str_starts_with($value, '@')) {
                    $value = preg_replace('/(@)?{{\s*(.+?)\s*}}(\r?\n)?/s', '$2', $value);
                    $attributeName = ':' . $attributeName;
                }
            }

            $attributes[$attributeName] = $value;
        }

        return $attributes;
    }

    protected function compileComponentTag($content)
    {
        return preg_replace_callback("/
        <
            \s*
            component[-\:]([\w\-\:\.]*)
            (?<attributes>
                (?:
                    \s+
                    (?:
                        (?:
                            @(?:class)(\( (?: (?>[^()]+) | (?-1) )* \))
                        )
                        |
                        (?:
                            \{\{\s*\\\$attributes(?:[^}]+?)?\s*\}\}
                        )
                        |
                        (?:
                            (\:\\\$)(\w+)
                        )
                        |
                        (?:
                            [\w\-:.@]+
                            (
                                =
                                (?:
                                    \\\"[^\\\"]*\\\"
                                    |
                                    \'[^\']*\'
                                    |
                                    [^\'\\\"=<>]+
                                )
                            )?
                        )
                    )
                )*
                \s*
            )
            (?<![\/=\-])
        >
    /x", function ($matches) {
            return $this->handleComponent(
                $matches[1],
                $this->getAttributesFromString($matches['attributes'])
            );
        }, $content);
    }

    protected function compileComponentSelClosingTag($content)
    {
        return preg_replace_callback("/
        <
            \s*
            component[-\:]([\w\-\:\.]*)
            \s*
            (?<attributes>
                (?:
                    \s+
                    (?:
                        (?:
                            @(?:class)(\( (?: (?>[^()]+) | (?-1) )* \))
                        )
                        |
                        (?:
                            \{\{\s*\\\$attributes(?:[^}]+?)?\s*\}\}
                        )
                        |
                        (?:
                            (\:\\\$)(\w+)
                        )
                        |
                        (?:
                            [\w\-:.@]+
                            (
                                =
                                (?:
                                    \\\"[^\\\"]*\\\"
                                    |
                                    \'[^\']*\'
                                    |
                                    [^\'\\\"=<>]+
                                )
                            )?
                        )
                    )
                )*
                \s*
            )
        \/>
    /x", function ($matches) {
            return $this->handleComponent(
                $matches[2],
                $this->getAttributesFromString($matches[3])
            ) . PHP_EOL . $this->closeTag();
        }, $content);
    }

    protected function compileComponentCloseTag($content)
    {
        return preg_replace("/<\/\s*component[-\:][\w\-\:\.]*\s*>/", $this->closeTag(), $content);
    }

    protected function compileSlots($content)
    {

        $value = preg_replace_callback("/<\s*slot[-\:]([\w\-\:\.]*)\s*(.*?)(?<![\/=\-])>/x", function ($matches) {
            return $this->handleSlot(
                $matches[1],
                $this->getAttributesFromString($matches[2])
            );
        }, $content);

        return preg_replace("/<\/\s*slot[-\:][\w\-\:\.]*\s*>/", $this->closeSlot(), $value);
    }

    protected function handleSlot($name, $attributes)
    {

        if (count($names = explode('-', $name)) > 1) {
            $name = array_reduce($names, function ($cary, $name) {
                return $cary . ucfirst($name);
            }, '');
        }

        $name = lcfirst($name);

        return implode("\n", [
            "<?php \${$name} = \$_engine->slot('{$name}'," . '"' . $this->attributesToString($attributes) . '"' . "); ?>",
            "<?php \$_engine->startSlot('{$name}') ?>",
        ]);
    }

    protected function closeSlot()
    {
        return "\n" . "<?php \$_engine->endSlot() ?>";
    }
}