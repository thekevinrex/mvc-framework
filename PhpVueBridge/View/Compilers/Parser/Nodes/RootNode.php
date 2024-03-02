<?php

namespace PhpVueBridge\View\Compilers\Parser\Nodes;

class RootNode extends ModuleNode
{

    public function __construct(
        array $nodes = []
    ) {
        $this->nodes = $nodes;
    }

    public function getTokenType(): string
    {
        return 'root';
    }
}
