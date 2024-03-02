<?php

namespace PhpVueBridge\Console\Output;

use PhpVueBridge\Console\Output\Output;
use PhpVueBridge\Console\Output\Line\OutputLine;
use PhpVueBridge\Console\Contracts\OutputContract;
use PhpVueBridge\Console\Interfaces\OutputInterface;
use PhpVueBridge\Console\Interfaces\OutputLineInterface;

class StyleOutput implements OutputInterface
{

    protected OutputContract $output;

    public function line(string $message, ?string $style = null): OutputLineInterface
    {
        return $this->getDefaultOutputLine($message, $style);
    }

    public function newLine(int $count = 1): void
    {
        $this->getOutput()->newLine($count);
    }

    public function writeLine(array|string|OutputLineInterface $message, ?string $style = null): void
    {
        $this->getOutput()->writeLine($message, $style);
    }

    public function comment(array|string $message): void
    {
        $this->getOutput()->writeLine($message, 'comment');
    }

    public function info(array|string $message): void
    {
        $this->getOutput()->info($message);
    }


    public function getOutput(): OutputContract
    {
        return $this->output ??= new Output();
    }

    protected function getDefaultOutputLine(string $message, ?string $style = null): OutputLineInterface
    {
        return new OutputLine($message, $style);
    }
}
?>