<?php


namespace app\core\view\Compilers;

use app\core\view\Compilers\Managers\compileComponent;
use app\core\view\Compilers\Managers\compileEchos;
use app\core\view\Compilers\Managers\compileRaws;

class ComponentCompiler extends Compiler
{
    use compileComponent,
        compileRaws,
        compileEchos;

    protected array $compileMethods = [
        'Echos',
        'RawPhp',
        'Script',
    ];

    public function compile(string $content): string
    {


        return $content;
    }

}