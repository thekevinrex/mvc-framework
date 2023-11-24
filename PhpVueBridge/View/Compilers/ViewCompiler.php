<?php


namespace app\core\view\Compilers;

use app\Core\Support\Util;
use app\core\view\Compilers\ComponentTagCompiler;
use app\core\view\Compilers\Managers\compileEchos;
use app\core\view\Compilers\Managers\compileFiles;
use app\core\view\Compilers\Managers\compileRaws;

class ViewCompiler extends Compiler
{
    use compileFiles,
        compileRaws,
        compileEchos;

    protected array $compileMethods = [
        'Files',
        'Blocks',
        'Components',
        'Echos',
        'RawPhp',
        'Script',
    ];

    public function compile(string $content): string
    {


        return $content;
    }

    protected function compileComponents($content)
    {
        return (new ComponentTagCompiler($this->componentNamespace))->compile($content);
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

    public function assets($matches)
    {
        $inputs = $matches[2];

        if (str_starts_with($matches[2], '[') && str_ends_with($matches[2], ']')) {
            $inputs = substr($matches[2], 1, -1);
        }

        $inputs = explode(',', $inputs);

        return \app\core\facades\Bridge::loadAssets($inputs);
    }

    public function vueProps($matches)
    {
        return "<?php echo \$_engine->setVueProps ({$matches[2]}) ?>";
    }

    public function ref($matches)
    {
        return "<?php echo \$_engine->defineRef({$matches[2]}) ?>";
    }

    public function computed($matches)
    {
        return "<?php echo \$_engine->computed({$matches[2]}) ?>";
    }

    public function endComputed()
    {
        return "<?php echo \$_engine->endComputed() ?>";
    }

    public function method($matches)
    {
        return "<?php echo \$_engine->method({$matches[2]}) ?>";
    }

    public function endMethod()
    {
        return "<?php echo \$_engine->endMethod () ?>";
    }
}