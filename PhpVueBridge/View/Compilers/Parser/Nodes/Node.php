<?php

namespace PhpVueBridge\View\Compilers\Parser\Nodes;

abstract class Node
{

    protected bool $preCompiled = false;

    abstract public function getTokenType(): string;

    abstract public function compile(): array;

    abstract public function preCompile(): void;

    abstract public function toString(): string;

    public function isPreCompiled(): bool
    {
        return $this->preCompiled;
    }

    public function __toString()
    {
        $string = '<ul>'
            . get_class($this)
            . ($this->preCompiled ? ' :ok ' : ' ')
            . "(";

        if ($this instanceof ModuleNode) {
            $string .= PHP_EOL . $this->toString() . ")";
        } else {
            $string .= $this->toString() . ')';
        }

        $string .= '</ul>';

        return $string;
    }

    public function getParent(): ?Node
    {
        if (property_exists($this, 'parent')) {
            return $this->parent;
        }

        return null;
    }
}
