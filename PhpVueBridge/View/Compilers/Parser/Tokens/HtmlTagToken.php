<?php

namespace PhpVueBridge\View\Compilers\Parser\Tokens;

use PhpVueBridge\View\Compilers\Parser\Token;

class HtmlTagToken extends Token
{

    protected string $trueTagName;

    protected array $attrs = [];

    protected array $attributes;

    public function __construct(
        string $token,
        string $content,
        protected string $tagName
    ) {

        parent::__construct($token, $content);

        $this->parseAttributesFromContent();
    }

    public function getTagName()
    {
        return $this->tagName;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    protected function parseShortAttributeSyntax(string $value)
    {
        return preg_replace_callback(
            "/\s\:\\\$(\w+)/x",
            function (array $matches) {
                return " :{$matches[1]}=\"\${$matches[1]}\"";
            },
            $value
        );
    }

    protected function parseAttributesFromContent()
    {
        $value = $this->content;

        $this->parseShortAttributeSyntax($value);

        $regex = "/
            (?<attribute>[\w\-:.@]+)
            (
                =
                (?<value>
                    (
                        \"[^\"]+\"
                        |
                        \\\'[^\\\']+\\\'
                        |
                        [^\s>]+
                    )
                )
            )?
        /x";

        preg_match_all($regex, $value, $matches, PREG_SET_ORDER);

        $attributes = [];

        $this->trueTagName = array_shift($matches)['attribute'] ?? null;

        foreach ($matches as $match) {

            if (!isset($match['value']) || !isset($match['attribute'])) {

                if (isset($match['attribute'])) {
                    $this->attrs[] = $match['attribute'];
                }

                continue;
            }

            $attributeName =
                (str_starts_with($match["attribute"], '::'))
                ? substr($match["attribute"], 1)
                : $match["attribute"];

            $value = substr($match["value"], 1, -1);

            $attributes[$attributeName] = $value;
        }

        $this->attributes = $attributes;
    }
}
