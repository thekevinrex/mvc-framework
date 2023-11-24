<?php


namespace app\core\view\Engines;

use app\core\view\Engines\CompilerEngine;


class ComponentEngine extends PhPEngine
{

    protected CompilerEngine $engine;

    public function __construct(CompilerEngine $engine)
    {
        $this->engine = $engine;
    }

}