<?php

namespace PhpVueBridge\Console\Output;

use PhpVueBridge\Console\Output\Line\OutputLine;
use PhpVueBridge\Console\Contracts\OutputContract;
use PhpVueBridge\Console\Interfaces\FormaterInterface;


class Output implements OutputContract
{

    public function __construct(
        protected ?FormaterInterface $formater = null
    ) {

    }

    public function info(string|array $message)
    {
        $this->block($message, 'INFO', 'bg=green');
    }

    public function newLine(int $count = 1)
    {
        $this->write(str_repeat(PHP_EOL, $count));
    }

    public function writeLine($message, $style = null)
    {
        $message = is_array($message) ? $message : [$message];

        $this->newLine();

        foreach ($message as $key => $line) {

            if ($line instanceof \Closure) {
                $line = $line();
            }

            if (!$line instanceof OutputLine) {
                $line = new OutputLine($line, $style);
            }

            $this->write($line, true);
        }
    }

    public function block(string|array $message, string $type = null, string $style = null)
    {
        $message = is_array($message) ? $message : [$message];

        $this->newLine();
        $this->writeLine($this->createBlock($message, $type, $style));
        $this->newline();
    }

    protected function createBlock(array $message, string $type = null, string $style = null)
    {
        $ident = 0;
        if (!is_null($type)) {
            $type = sprintf('[%s] ', $type);
            $ident = strlen($type);
        }

        $lines = [];

        foreach ($message as $key => $line) {

            $lines[] = new OutputLine($line);

            if (count($message) > 1 && $key < count($message) - 1) {
                $lines[] = '';
            }
        }

        $lineIdent = str_repeat(' ', $ident);

        foreach ($lines as $key => &$line) {
            if ($line instanceof OutputLine && !is_null($type) && $key == 0) {

                $line->prepend($type);


                if (!is_null($style)) {
                    $line->style($style);
                }
            }


        }

        return $lines;
    }

    protected function write(OutputLine|string $line, bool $newLine = false)
    {
        fwrite(
            STDOUT,
            $line
        );

        if ($newLine) {
            $this->newLine();
        }
    }
}
?>