<?php


namespace PhpVueBridge\View\Compilers;

use PhpVueBridge\View\Compilers\Managers\compileCommon;
use PhpVueBridge\View\Compilers\Managers\compileComponent;
use PhpVueBridge\View\Compilers\Managers\CompileCss;
use PhpVueBridge\View\Compilers\Managers\compileFiles;
use PhpVueBridge\View\Compilers\Managers\compileEchos;
use PhpVueBridge\View\Compilers\Managers\CompileLoops;
use PhpVueBridge\View\Compilers\Managers\CompileConditionals;

class ViewCompiler extends Compiler
{
    use compileFiles,
        compileCommon,
        CompileCss,
        CompileConditionals,
        CompileLoops,
        compileComponent,
        compileEchos;

    protected array $compileMethods = [
        'files',
        'blocks',
        'Echos',
    ];
}
