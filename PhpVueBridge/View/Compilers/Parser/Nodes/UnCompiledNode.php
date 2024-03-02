<?php

namespace PhpVueBridge\View\Compilers\Parser\Nodes;

use PhpVueBridge\View\Compilers\Parser\Token;

class UnCompiledNode extends Node
{
    public function __construct(
        protected Token $token,
        protected ?ModuleNode $parent,
    ) {
    }

    public function getToken(): Token
    {
        return $this->token;
    }

    public function getTokenType(): string
    {
        return $this->token->getType();
    }

    public function compile(): array
    {
        return [$this->token->getContent()];
    }

    public function toString(): string
    {
        return $this->token->getContent();
    }

    public function preCompile(): void
    {
        $this->preCompiled = true;
    }
}
