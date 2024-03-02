<?php


namespace PhpVueBridge\View\Engines;

class ComponentEngine extends PhPEngine
{

    protected CompilerEngine $engine;

    public function __construct(CompilerEngine $engine)
    {
        $this->engine = $engine;
    }

}