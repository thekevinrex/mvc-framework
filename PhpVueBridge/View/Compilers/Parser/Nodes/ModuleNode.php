<?php

namespace PhpVueBridge\View\Compilers\Parser\Nodes;

use PhpVueBridge\View\Compilers\Parser\Token;

class ModuleNode extends Node
{
    protected array $nodes = [];

    protected Token $token;

    protected ?ModuleNode $parent;

    protected array $inner = [];

    protected int $current = 0;

    public function __construct(
        Token $token,
        ?ModuleNode $parent = null,
        array $inner = []
    ) {
        $this->token = $token;
        $this->parent = $parent;
        $this->inner = $inner;
    }

    public function compile(): array
    {
        $compiled = [];

        if (isset($this->token) && !$this instanceof ComponentNode) {
            $compiled[] = $this->token->getContent();
        }

        foreach ($this->nodes as $node) {
            $compiled = array_merge(
                $compiled,
                $node->compile()
            );
        }

        if (isset($this->token) && !$this instanceof ComponentNode && $this->token->getType() !== Token::AUTO_CLOSE_TAG_TOKEN) {
            $compiled[] = "</{$this->token->getTagName()}>";
        }

        return $compiled;
    }

    public function toString(): string
    {
        $string = (isset($this->token) ? '<ul>' . e($this->token->getContent()) . '</ul>' : '');

        $string .= '<ul>' . (count($this->inner) > 0 ? 'Inner: ' : '');
        foreach ($this->inner as $node) {
            $string .= '<li style="list-style:none">' . $node . '</li>';
        }

        $string .= (count($this->nodes) > 0 ? 'Nodes: ' : '');
        foreach ($this->nodes as $node) {
            $string .= '<li style="list-style:none">' . $node . '</li>';
        }

        $string .= '</ul>';

        return $string;
    }

    public function getChilds()
    {
        return $this->nodes;
    }

    public function setChildNodes(array $nodes)
    {
        $this->nodes = $nodes;

        return $this;
    }

    public function getToken(): Token
    {
        return $this->token;
    }

    public function getTokenType(): string
    {
        return $this->token->getType();
    }

    public function getCurrentNode(): Node
    {
        return $this->nodes[$this->current];
    }

    public function getNextNode(): ?Node
    {
        if ($this->current + 1 >= count($this->nodes)) {
            return null;
        }

        return $this->nodes[$this->current + 1];
    }

    public function nextNode(): void
    {
        $this->current++;
    }

    public function isLastNode(): bool
    {
        return $this->current >= count($this->nodes);
    }

    public function preCompile(): void
    {

        $this->preCompiled = true;
    }
}
