<?php

namespace PhpVueBridge\View\Errors;

use PhpVueBridge\Logger\StdOutLogger;
use PhpVueBridge\Logger\Utils\LoggerLevel;
use PhpVueBridge\View\Engines\PhPEngine;

class ErrorRender extends PhPEngine
{

    public function __construct(
        protected \Throwable $e
    ) {
    }

    protected function getData(): array
    {
        return [
            'message' => $this->e->getMessage(),
            'code' => $this->e->getCode(),
        ];
    }

    protected function getDebugData(): array
    {
        return [
            ...$this->getData(),
            'debugMode' => [
                'file' => $this->e->getFile(),
                'line' => $this->e->getLine(),
                'trace' => $this->e->getTrace(),
            ]
        ];
    }

    protected function getFile(): string
    {
        return __DIR__ . match ($this->e->getCode()) {
            default => '/views/error.phtml'
        };
    }

    protected function log($level = LoggerLevel::ERROR, $message = null): void
    {
        (new StdOutLogger())->logFile(LoggerLevel::ERROR, $message ?? "An error has occurred : {$this->e}");
    }

    public function render($log = true)
    {
        if ($log) {
            $this->log();
        }

        return $this->get($this->getFile(), $this->getData());
    }

    public function debug()
    {
        $this->log(LoggerLevel::DEBUG);

        return $this->get(__DIR__ . '/views/error.phtml', $this->getDebugData());
    }
}
?>