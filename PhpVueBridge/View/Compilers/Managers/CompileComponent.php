<?php

namespace app\core\view\Compilers\Managers;

use app\Core\Support\Util;

trait compileComponent
{

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
?>