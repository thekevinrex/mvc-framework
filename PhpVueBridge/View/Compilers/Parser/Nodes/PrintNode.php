<?php

namespace PhpVueBridge\View\Compilers\Parser\Nodes;

use PhpVueBridge\View\Compilers\Parser\Token;

class PrintNode extends Node
{

    public function __construct(
        protected array $expresions,
        protected ?ModuleNode $parent
    ) {
    }

    public function getTokenType(): string
    {
        return Token::EXPRESION_TOKEN;
    }

    public function compile(): array
    {
        return array_map(fn ($expresion) => "<?php echo {$expresion} ?>", $this->expresions);
    }

    public function toString(): string
    {
        return implode(PHP_EOL, $this->expresions);
    }

    public function preCompile(): void
    {
        $this->preCompiled = true;
    }
}
