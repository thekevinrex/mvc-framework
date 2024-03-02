<?php

namespace PhpVueBridge\View\Compilers\Parser;

use PhpVueBridge\View\Compilers\Exceptions\ParserException;

class TokenStream
{

    protected int $current;

    protected array $tokens;

    protected ?Token $prevToken;

    public function __construct(
        protected Parser $parser,
    ) {

        $this->current = -1;

        $this->tokens = $parser->getTokens();
    }

    public function getCurrent(): ?Token
    {
        return $this->tokens[$this->current] ?? null;
    }

    public function getCurrentType(): string
    {
        return $this->tokens[$this->current]->getType();
    }

    public function next(): Token
    {
        $next = $this->current + 1;

        if (!isset($this->tokens[$next])) {
            throw new ParserException("Unexpected end of token");
        }

        $this->prevToken = $this->getCurrent();

        $this->current = $next;

        return $this->tokens[$next];
    }

    public function isFinished(): bool
    {
        return $this->getCurrentType() === Token::EOF_TOKEN;
    }

    public function injectTokens(array $tokens)
    {
        $this->tokens = array_merge(\array_slice($this->tokens, 0, $this->current), $tokens, \array_slice($this->tokens, $this->current));
    }

    public function __toString()
    {
        return '<ul>'
            . implode(
                PHP_EOL,
                array_map(fn ($token) => "<li style='list-style:none'>{$token}</li>", $this->tokens)
            )
            . '</ul>';
    }
}
