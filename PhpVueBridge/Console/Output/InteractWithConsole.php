<?php

namespace PhpVueBridge\Console\Output;

use PhpVueBridge\Console\Output\StyleOutput;
use PhpVueBridge\Console\Interfaces\OutputInterface;
use PhpVueBridge\Console\Interfaces\OutputLineInterface;

trait InteractWithConsole
{

    protected OutputInterface $output;

    public function comment(string|array $message)
    {
        $this->getOutput()->comment($message);
    }

    public function info(array|string $message)
    {
        $this->getOutput()->info($message);
    }

    public function writeLine(array|string|OutputLineInterface $message, string $style = null)
    {
        $this->getOutput()->writeLine($message, $style);
    }

    public function line(string $message, string $style = null)
    {
        return $this->getOutput()->line($message, $style);
    }

    public function newLine(int $count = 1)
    {
        return $this->getOutput()->newLine($count);
    }

    public function getOutput(): OutputInterface
    {
        return $this->output ??= new StyleOutput();
    }
}
?>