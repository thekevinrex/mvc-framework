<?php

namespace PhpVueBridge\View\Compilers\Parser;

use Closure;

class ExpresionParser
{
    protected Closure $callback;

    protected string $expresion;

    protected array $tokens = [];

    // The current position in the expresion
    protected int $current;

    // The length of the expresion
    protected int $length;

    // The buffering
    protected string $buffer = '';

    protected array $bufferWord = [];

    // The total of parentheses found
    protected int $totalOpen = 0;

    protected array $posibleOperators = [];

    protected array $operators = array(
        '**' => 'Exponente',
        '*'  => 'Multiplicación',
        '/'  => 'División',
        '%'  => 'Módulo',
        '+'  => 'Suma',
        '-'  => 'Resta',
        '>=' => 'Mayor o igual que',
        '>'  => 'Mayor que',
        '<=' => 'Menor o igual que',
        '<'  => 'Menor que',
        '===' => 'Estrictamente igual a',
        '==' => 'Igual a',
        '!==' => 'Estrictamente diferente de',
        '!=' => 'Diferente de',
    );

    protected array $operations = array(
        '.' => 'Concatenacion de string',
        '->' => '',
        '=>' => '',
        '?' => '',
        ':' => '',
    );

    public function __construct(
        protected string $content
    ) {
    }

    public function parse(Closure $callback)
    {
        $this->callback = $callback;
        $this->current = 0;

        [$filters, $expresion] = $this->parseExpresionAndFilters();

        $this->expresion = trim($expresion);

        $this->length = strlen($this->expresion);

        $this->parseExpresion();

        echo '<pre>';
        var_dump(array_map(fn ($token) => e(json_encode($token)), $this->tokens));
        echo '<pre>';
    }

    protected function parseExpresionAndFilters()
    {

        // From the expresion split all the filters
        $filters = preg_split('/\s*\|\s*(?![^(]*\))/', $this->content);

        // the first filter should be the expresion
        $expresion = array_shift($filters);


        return [$filters, $expresion];
    }

    protected function getAllPosibleOperators(): void
    {

        $regex = '#' .
            implode(
                '|',
                array_map('preg_quote', array_merge(
                    array_keys($this->operations),
                    array_keys($this->operators),
                ))
            ) . '#';

        preg_match_all($regex, $this->expresion, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
    }

    protected function parseExpresion(): void
    {
        // $str | str + 5 | str ( $str )
        // str  | $str + fg.as | juan(5) + 44
        // "asda asdas" . 'qweqw'

        $this->getAllPosibleOperators();
        return;

        while ($this->current < $this->length) {

            $char = $this->expresion[$this->current];

            if ($char === ' ') {
                $this->addSpace();
                continue;
            }

            // if is in the array of operators it must be a operator
            if (in_array($char, array_keys($this->operadores))) {

                // if we reach a operator then the buffer must be a variable
                $this->addBuffer($this->buffer);

                // Add the operator type
                $this->tokens[] = [Type::TYPE_OPERATOR, $char];

                // increment the current and continue
                $this->current++;
                continue;
            }

            if ($char === '(') {

                if (!empty($this->buffer)) {
                    $this->tokens[] = [Type::TYPE_FUNCTION, $this->buffer];
                }

                $this->tokens[] = [Type::TYPE_START_FUNCTION, '('];

                $this->buffer = '';
                $this->current++;
                continue;
            }

            if ($char === ')') {

                $this->addBuffer($this->buffer);

                $this->tokens[] = [Type::TYPE_END_FUNCTION, ')'];
                $this->current++;
                continue;
            }

            if ($char === '"') {
                $this->addString();
                continue;
            }

            if ($char === "'") {
                $this->addString("'");
                continue;
            }

            $this->buffer .= $char;
            $this->current++;
        }

        $this->addBuffer($this->buffer);
    }

    protected function addSpace()
    {
        $this->bufferWord[] = $this->buffer;
        $this->buffer = '';
    }

    protected function addString($search = '"')
    {

        $current = $this->current;

        $till = strpos($this->expresion, $search, $current + 1);

        $this->tokens[] = [
            Type::TYPE_STRING,
            $sub = substr($this->expresion, $current + 1, $till - $current - 1),
        ];

        $this->current += strlen($sub) + 2;
        $this->buffer = '';
    }

    protected function addBuffer(string $buffer)
    {
        $buffer = trim($buffer);

        if (empty($buffer)) {
            return;
        }

        // If the expresion it only contains numbers then it must be a number :>
        if (preg_match('/^\d+$/', $buffer)) {
            $this->tokens[] = [Type::TYPE_NUMBER, $buffer];
        }

        // If not then it must be a variable
        else {
            $this->tokens[] = [
                Type::TYPE_VAR,
                (str_contains($buffer, '$'))
                    ? $buffer
                    : '$' . $buffer
            ];
        }

        $this->buffer = '';
    }
}
