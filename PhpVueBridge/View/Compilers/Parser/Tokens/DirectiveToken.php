<?php

namespace PhpVueBridge\View\Compilers\Parser\Tokens;

use PhpVueBridge\View\Compilers\Parser\Token;

class DirectiveToken extends Token
{

    protected array $parsedArguments = [];

    protected string $directive;

    public function __construct(
        string $token,
        string $content,
        protected ?string $arguments = ''
    ) {
        $this->parseArguments();

        $this->directive = $content;

        $content = '@' . $content . ' ' . $arguments;

        parent::__construct($token, $content);
    }

    public function getDirective()
    {
        return $this->directive;
    }

    public function getArguments(): array
    {
        return $this->parsedArguments;
    }

    protected function parseArguments()
    {
        // First we strip the parentheses from the arguments
        $arguments = substr($this->arguments, 1, -1);

        $arguments = trim($arguments);

        $length = strlen($arguments);
        $pos = 0;
        $buffer = '';
        $arg = [];

        while ($pos < $length) {

            $current = substr($arguments, $pos, 1);

            if ($current == '(') {
                $end = $this->findEvenChar($arguments, '(', ')', $pos);
                $current = substr($arguments, $pos, $end - $pos);
            }

            if ($current == '[') {
                $end = $this->findEvenChar($arguments, '[', ']', $pos);
                $current = substr($arguments, $pos, $end - $pos);
            }

            if ($current == '\'') {
                $end = $this->findEvenChar($arguments, "'", "'", $pos);
                $current = substr($arguments, $pos, $end - $pos);
            }

            if ($current == '"') {
                $end = $this->findEvenChar($arguments, '"', "'", $pos);
                $current = substr($arguments, $pos, $end - $pos);
            }

            if ($current == ',') {
                $arg[] = trim($buffer);
                $buffer = '';
            } else {
                $buffer .= $current;
            }

            $pos += strlen($current);
        }

        if (!empty($buffer)) {
            $arg[] = trim($buffer);
        }

        $this->parsedArguments = $arg;
    }

    protected function findEvenChar(string $content, string $charO, string $charC, int $start): int
    {

        $total = 1;
        $length = strlen($content);
        $pos = $start + 1;

        while ($total > 0 && $pos < $length) {

            $current = $content[$pos];

            if ($current == $charO) {
                $total++;
            }

            if ($current == $charC) {
                $total--;
            }

            $pos++;
        }

        return $pos;
    }
}
