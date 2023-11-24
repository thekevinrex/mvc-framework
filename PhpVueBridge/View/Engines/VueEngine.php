<?php

namespace app\Core\view\Engines;

use app\core\view\Engines\Managers\VueManager;

class VueEngine
{

    use VueManager;

    protected CompilerEngine $engine;


    public function __construct(CompilerEngine $engine)
    {
        $this->engine = $engine;
    }

}
?>