<?php


namespace PhpVueBridge\Console;

use PhpVueBridge\Logger\Logger;

class ConsoleLogger extends Logger
{

    public function log($level, string $message, array $context = []): void
    {
        $level = $level ?? LoggerLevel::ERROR;

        $output = LoggerLevel::OUTMAP[$level] ?? STDERR;

        fwrite(
            $output,
            $this->formatOutPut($level, $message) . PHP_EOL,
        );
    }


    public function formatOutPut($level, string $message): string
    {

    }

}

?>