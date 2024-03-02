<?php


namespace PhpVueBridge\View\Compilers;

use PhpVueBridge\Support\Util;
use PhpVueBridge\View\Compilers\Managers\compileComponent;
use PhpVueBridge\View\Compilers\Managers\compileRaws;

class VueCompiler extends Compiler
{
    use compileComponent,
        compileRaws;

    protected array $compileMethods = [];

    public function vueProps($matches)
    {
        return "<?php echo \$_engine->setVueProps ({$matches[2]}) ?>";
    }

    public function data($matches)
    {

        $expresion = Util::StripString($matches[2]);

        if (!str_contains($expresion, '$')) {
            $expresion = "$" . $expresion;
        }

        return "<?php echo e({$expresion}) ?>";
    }

    public function ref($matches)
    {
        return "<?php echo \$_engine->defineRef({$matches[2]}) ?>";
    }
}
