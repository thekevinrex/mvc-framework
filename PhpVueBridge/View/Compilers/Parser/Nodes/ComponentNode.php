<?php

namespace PhpVueBridge\View\Compilers\Parser\Nodes;

use PhpVueBridge\View\ComponentFinder;
use PhpVueBridge\View\Compilers\Parser\Token;
use PhpVueBridge\View\Compilers\Parser\Tokens\DirectiveToken;

class ComponentNode extends ModuleNode
{

    public function preCompile(): void
    {

        // Precompile the inner directives and expressions

        $this->nodes = array_merge(
            $this->handleComponent(),
            $this->nodes,
            $this->closeDirective(),
        );

        $this->preCompiled = true;
    }

    protected function handleComponent()
    {
        return [
            new TemplateNode(
                new UnCompiledNode(
                    new DirectiveToken(
                        Token::DIRECTIVE_TOKEN,
                        "component",
                        $this->compileDirective()
                    ),
                    $this,
                )
            )
        ];
    }

    protected function compileDirective(): string
    {
        $class = ComponentFinder::getComponentResolver($tagName = $this->token->getTagName());
        $attributes = $this->attributesToString($this->token->getAttributes());

        return "({$tagName},{$class},{$attributes})";
    }

    protected function closeDirective()
    {
        return [
            new TemplateNode(
                new UnCompiledNode(
                    new DirectiveToken(Token::DIRECTIVE_TOKEN, "endComponent"),
                    $this
                )
            )
        ];
    }

    protected function attributesToString($attributes)
    {
        $attributesString = [];
        foreach ($attributes as $key => $attribute) {
            $attributesString[] = "{$key}={$attribute}";
        }

        return implode(',', $attributesString);
    }
}
