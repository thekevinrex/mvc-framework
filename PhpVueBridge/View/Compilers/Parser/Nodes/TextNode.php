<?php

namespace PhpVueBridge\View\Compilers\Parser\Nodes;

use PhpVueBridge\View\Compilers\Parser\Token;

class TextNode extends Node
{

    public function __construct(
        protected Token|array $token,
        protected ?ModuleNode $parent,
        protected array $inner = [],
    ) {
        if (!is_array($token)) {
            $this->token = [$token];
        }
    }

    public function getTokenType(): string
    {
        return Token::TEXT_TOKEN;
    }

    public function compile(): array
    {
        return array_map(fn ($token) => $token->getContent(), $this->token);
    }

    public function toString(): string
    {
        $string = '[<ul>';

        foreach ($this->token as $token) {
            $string .= PHP_EOL . '<li>' . e($token->getContent()) . '</li>';
        }

        $string .= '</ul>]';
        return $string;
    }

    public function preCompile(): void
    {
        $this->preCompiled = true;
    }
}
