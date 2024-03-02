<?php


namespace PhpVueBridge\View\Compilers;

use PhpVueBridge\View\ComponentFinder;
use PhpVueBridge\View\AnonymosComponent;
use PhpVueBridge\View\Compilers\Parser\Nodes\ModuleNode;
use PhpVueBridge\View\Compilers\Parser\Nodes\TemplateNode;
use PhpVueBridge\View\Compilers\Parser\Nodes\UnCompiledNode;
use PhpVueBridge\View\Compilers\Parser\Token;
use PhpVueBridge\View\Compilers\Parser\Tokens\DirectiveToken;
use PhpVueBridge\View\Compilers\Parser\TreeNavegator;

class ComponentTagCompiler
{

    public function __construct(
        protected TreeNavegator $navegator
    ) {
    }

    public function compile()
    {

        $this->navegator
            ->filterByTokens([Token::AUTO_CLOSE_CUSTOM_TAG_TOKEN, Token::OPEN_CUSTOM_TAG_TOKEN])
            ->start(function (TemplateNode $node, $navegator) {

                // The auto close custom tag and the open custom tag
                // are placed inside a template node, so we need to access the template node 
                // To get the component node
                $componentNode = $node->getNode();

                // Get the component token 
                // This has the information of tagName, attributes
                $token = $componentNode->getToken();

                $tagName = $token->getTagName();
                $attributes = $token->getAttributes();

                $nodes = array_merge(
                    $this->handleComponent($tagName, $attributes),
                    $componentNode->getChilds()
                );

                if ($node->getTokenType() == Token::AUTO_CLOSE_CUSTOM_TAG_TOKEN) {
                    array_push($this->closeDirective());
                }

                $newNode = new ModuleNode(
                    $token
                );

                $node->setNode($newNode);
            });

        // $content = $this->compileSlots($content);
        // $content = $this->compileComponentSelClosingTag($content);
        // $content = $this->compileComponentCloseTag($content);
        // $content = $this->compileComponentTag($content);

        // return $content;
    }

    protected function handleComponent($component, $attributes = []): array
    {
        $class = ComponentFinder::getComponentResolver($component);

        return [
            new TemplateNode(
                new UnCompiledNode(
                    new DirectiveToken(Token::DIRECTIVE_TOKEN, $this->compileDirective($component, $class, $attributes)),
                    $this->navegator->getCurrent()->getParent()
                )
            )
        ];
    }

    protected function compileDirective($name, $class, $attributes)
    {
        return "@component({$name},{$class},{$this->attributesToString($attributes)})";
    }

    protected function closeDirective()
    {
        return [
            new TemplateNode(
                new UnCompiledNode(
                    new DirectiveToken(Token::DIRECTIVE_TOKEN, "@endComponent"),
                    $this->navegator->getCurrent()->getParent()
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

    protected function compileSlots($content)
    {

        $value = preg_replace_callback("/<\s*slot[-\:]([\w\-\:\.]*)\s*(.*?)(?<![\/=\-])>/x", function ($matches) {
            return $this->handleSlot(
                $matches[1],
                $this->getAttributesFromString($matches[2])
            );
        }, $content);

        return preg_replace("/<\/\s*slot[-\:][\w\-\:\.]*\s*>/", $this->closeSlot(), $value);
    }

    protected function handleSlot($name, $attributes)
    {

        if (count($names = explode('-', $name)) > 1) {
            $name = array_reduce($names, function ($cary, $name) {
                return $cary . ucfirst($name);
            }, '');
        }

        $name = lcfirst($name);

        return "@slot('{$name}',{$this->attributesToString($attributes)})";
    }

    protected function closeSlot()
    {
        return PHP_EOL . "@endSlot";
    }
}
