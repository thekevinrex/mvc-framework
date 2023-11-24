<?php


namespace app\core\view\Compilers;

use app\core\view\Compilers\Managers\compileComponent;
use app\core\view\Compilers\Managers\compileRaws;

class VueCompiler extends Compiler
{
    use compileComponent,
        compileRaws;

    protected array $compileMethods = [
        'RawPhp',
        'Script',
    ];

    public function compile(string $content): string
    {


        return $content;
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