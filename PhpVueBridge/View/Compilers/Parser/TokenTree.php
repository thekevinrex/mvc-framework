<?php

namespace PhpVueBridge\View\Compilers\Parser;

use PhpVueBridge\View\Compilers\Parser\Nodes\Node;
use PhpVueBridge\View\Compilers\Parser\Nodes\RootNode;
use PhpVueBridge\View\Compilers\Parser\Nodes\TextNode;
use PhpVueBridge\View\Compilers\Parser\Nodes\PrintNode;
use PhpVueBridge\View\Compilers\Parser\Nodes\ModuleNode;
use PhpVueBridge\View\Compilers\Parser\Nodes\TemplateNode;
use PhpVueBridge\View\Compilers\Parser\Nodes\ComponentNode;
use PhpVueBridge\View\Compilers\Parser\Nodes\UnCompiledNode;

class TokenTree
{

    protected bool $isBuilded = false;

    protected RootNode $tree;

    protected ?ModuleNode $parent;

    public function __construct(
        protected TokenStream $stream
    ) {
    }

    public function preCompile(
        TreeNavegator $navegator,
        \Closure $compileDirective
    ) {
        $navegator
            ->start(function (Node $node) use ($compileDirective) {

                if ($node->isPreCompiled()) {
                    return;
                }

                if ($node->getTokenType() === Token::DIRECTIVE_TOKEN) {
                    $node->setNode(
                        $compileDirective($node)
                    );
                }

                $node->preCompile();
            });
    }

    public function compile()
    {
        echo '<pre>';
        var_dump($this->getTree()->compile());
        echo '</pre>';
        return implode(
            PHP_EOL,
            $this->getTree()->compile()
        );
    }

    public function getTree(): RootNode
    {
        if (!$this->isBuilded) {
            $this->build();
        }

        return $this->tree;
    }

    public function build(): self
    {

        $this->tree = $root = new RootNode();

        $this->parent = $root;

        $root->setChildNodes(
            $this->subNode()
        );

        $this->isBuilded = true;

        return $this;
    }

    protected function subNode(): array
    {
        // The sub tree node
        $nodes = [];

        // If a tag has inner directives or echos
        $inner = [];

        $this->stream->next();

        while (!$this->stream->isFinished()) {

            $type = $this->stream->getCurrentType();

            if ($type === Token::CLOSE_TAG_TOKEN) {
                break;
            }

            // If is a directive token then create a new UnCompiledNode for later compilation
            if ($type === Token::DIRECTIVE_TOKEN) {
                $nodes[] = new TemplateNode(
                    new UnCompiledNode($this->stream->getCurrent(), $this->parent)
                );
            }

            // If it a inner directive then we added to the 
            else if ($type === Token::IN_TAG_DIRECTIVE_TOKEN) {
                $inner[] = new UnCompiledNode($this->stream->getCurrent(), $this->parent);
            }

            // If is a 
            else if (in_array($type, [Token::OPEN_TAG_TOKEN, Token::AUTO_CLOSE_TAG_TOKEN])) {
                $node = new ModuleNode(
                    $this->stream->getCurrent(),
                    $this->parent,
                    $inner,
                );

                if ($type === Token::OPEN_TAG_TOKEN) {
                    $prevParent = $this->parent;

                    $this->parent = $node;

                    $node->setChildNodes($this->subNode());

                    $this->parent = $prevParent;
                }

                $inner = [];
                $nodes[] = $node;
            }

            // 
            else if (in_array($type, [Token::OPEN_CUSTOM_TAG_TOKEN, Token::AUTO_CLOSE_CUSTOM_TAG_TOKEN])) {
                $node = new ComponentNode(
                    $this->stream->getCurrent(),
                    $this->parent,
                    $inner
                );

                if ($type === Token::OPEN_CUSTOM_TAG_TOKEN) {
                    $prevParent = $this->parent;

                    $this->parent = $node;

                    $node->setChildNodes($this->subNode());

                    $this->parent = $prevParent;
                }

                $inner = [];
                $nodes[] = new TemplateNode(
                    $node
                );
            }

            // It it a text token then add a Text Node
            else if ($type === Token::TEXT_TOKEN) {
                $nodes[] = new TextNode($this->stream->getCurrent(), $this->parent);
            }

            // If it is a echo token then we create a print node
            else if (in_array($type, [Token::OPEN_CONTENT_TOKEN, Token::IN_TAG_OPEN_CONTENT_TOKEN])) {

                $expresions = [];

                while (!$this->stream->isFinished()) {

                    if (($token = $this->stream->next()) && $token->getType() !== Token::CLOSE_CONTENT_TOKEN) {
                        if ($token->getType() === Token::EXPRESION_TOKEN) {
                            $expresions[] = $token;
                        }
                    } else {
                        break;
                    }
                }

                if ($type === Token::IN_TAG_OPEN_CONTENT_TOKEN) {
                    $inner[] = new PrintNode($expresions, $this->parent);
                } else {
                    $nodes[] = new PrintNode($expresions, $this->parent);
                }
            }


            $this->stream->next();
        }

        return $nodes;
    }

    public function __toString()
    {
        return $this->getTree();
    }
}
