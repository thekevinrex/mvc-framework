<?php

namespace PhpVueBridge\View\Compilers\Parser\Nodes;

use PhpVueBridge\View\Compilers\Parser\Token;
use PhpVueBridge\View\Compilers\Parser\Tokens\HtmlTagToken;
use PhpVueBridge\View\Compilers\Parser\Tokens\TextToken;

class TemplateNode extends Node
{

    public function __construct(
        protected Node $node,
    ) {
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    public function setNode(Node $node): void
    {
        $this->node = $node;
    }

    public function getTokenType(): string
    {
        return $this->node->getTokenType();
    }

    public function preCompile(): void
    {
        $this->node->preCompile();

        $this->preCompiled = true;
    }

    public function compile(): array
    {
        return $this->node->compile();
    }

    public function toString(): string
    {
        return $this->node;
    }

    public function textNode(string|array $text): Node
    {
        $arr = (is_array($text)) ? $text : [$text];

        return new TextNode(
            array_map(fn ($text) => new TextToken(Token::TEXT_TOKEN, $text), $arr),
            $this->node->getParent()
        );
    }

    public function moduleNode(string $content, string $tagName, array $nodes): ModuleNode
    {
        return (new ModuleNode(
            new HtmlTagToken(Token::OPEN_TAG_TOKEN, $content, $tagName),
            $this->node->getParent(),
        ))->setChildNodes($nodes);
    }
}
