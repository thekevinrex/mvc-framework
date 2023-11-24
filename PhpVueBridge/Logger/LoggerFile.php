<?php

namespace PhpVueBridge\Logger;

use PhpVueBridge\Logger\Utils\LoggerLevel;

trait LoggerFile
{


    public function LogFile($level, string $message): void
    {

        $message = sprintf('[%s] %s', date('Y-m-d H:i:s'), $message) . PHP_EOL . PHP_EOL . PHP_EOL;

        if (in_array($level, LoggerLevel::LOGFILE)) {
            $this->logToFile($message);
        } else {
            if ($level === LoggerLevel::DEBUG) {
                $this->logToFile($message, 'app.debug');
            }
        }
    }

    protected function logToFile(string $message, $file = 'app.error'): void
    {
        $path = config('app.log_directory', '/log/');
        $storagePath = app()->getStoragePath();

        $log = fopen($storagePath . $path . "{$file}.log.txt", 'a');

        if (!$log) {
            return;
        }

        $length = strlen($message);

        try {
            fwrite($log, $message, $length);
        } finally {
            fclose($log);
        }
    }

}
?>