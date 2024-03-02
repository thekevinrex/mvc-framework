<?php

namespace PhpVueBridge\Console\Output\Color;

use PhpVueBridge\Console\Output\Formater\Formater;

class Color
{

    const COLORS = [
        'black' => '30',
        'red' => '31',
        'green' => '32',
        'yellow' => '33',
        'blue' => '34',
        'magenta' => '35',
        'cyan' => '36',
        'white' => '37',
    ];


    public function __construct(
        protected string $color
    ) {

    }

    public function getBg(): string
    {
        return $this->toAnsi(self::COLORS[$this->color] + 10);
    }

    public function getFg(): string
    {
        return $this->toAnsi(self::COLORS[$this->color]);
    }

    public function toAnsi($value): string
    {
        return sprintf(Formater::ANSI, $value);
    }
}
?>