<?php

namespace PhpVueBridge\Console\Interfaces;

use PhpVueBridge\Console\Contracts\OutputContract;
use PhpVueBridge\Console\Interfaces\OutputLineInterface;

interface OutputInterface
{
    public function line(string $message, ?string $style = null): OutputLineInterface;


    public function writeLine(array|string|OutputLineInterface $message, ?string $style = null): void;


    public function newLine(int $count = 1): void;

    public function getOutput(): OutputContract;

    public function comment(string|array $message): void;

    public function info(string|array $message): void;
}
?>