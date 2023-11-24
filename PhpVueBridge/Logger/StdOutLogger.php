<?php

namespace PhpVueBridge\Logger;

use PhpVueBridge\Logger\Utils\LoggerLevel;

class StdOutLogger extends Logger
{
    use LoggerFile;

    const LOGFORMAT = '%s [%s] - %s';

    public function log($level, string $message, array $context = []): void
    {
        $level = $level ?? LoggerLevel::ERROR;

        $output = LoggerLevel::OUTMAP[$level] ?? STDERR;

        if (method_exists($this, 'LogFile')) {
            $this->LogFile($level, $message);
        }

        fwrite(
            $output,
            $this->formatOutPut($level, $message) . PHP_EOL,
        );
    }

    protected function formatOutPut(string $level, $message): string
    {
        return sprintf(
            self::LOGFORMAT,
            date('Y-m-d H:i:s'),
            $level,
            $message
        );
    }
}
?>