<?php

namespace PhpVueBridge\Console\Commands;

use PhpVueBridge\Console\Command;
use PhpVueBridge\Console\Proccess\PhpExecutableFinder;
use PhpVueBridge\Console\Proccess\Proccess;


class ServeCommand extends Command
{

    protected int $port = 8000;

    protected int $offsetPort = 0;

    public function excecute()
    {
        // This start the process and returned the result for check if the process is still running
        $process = $this->startProcess();

        // To provide a funcionality similar to the host refresh that vite provides
        while ($process->isRunning()) {

            // TODO: Implement hot refresh for the environment and views

            usleep(500 * 1000);
        }

    }

    protected function startProcess(): Proccess
    {
        // Create a new instance of the process for the given command
        $process = new Proccess(
            $this->serveCommand(),
            $this->app->getBasePath() . '/public',
            $_ENV
        );

        // Start the process and pass the output handler to output when the process is started
        $process->start(function ($type, $data) {
            $this->handleOutput($type, $data);
        });

        return $process;
    }

    protected function serveCommand(): string
    {
        return implode(' ', [
            (new PhpExecutableFinder())->find(), // Find the php excecutable becouse the proc_open cant get access to php
            '-S', // Command to execute the local development server
            $this->host() . ':' . $this->port(), // The host and the port of the server
            '../server.php', // A fallback excecutable file
        ]);
    }

    public function host(): string
    {
        return '127.0.0.1';
    }

    public function port(): int
    {
        return $this->port + $this->offsetPort;
    }

    protected function handleOutput($type, $data)
    {
        if (!is_string($data) || empty($data)) {
            return;
        }

        // If the process output returns a string that contains the required string then we info that the server started
        if (str_contains($data, 'Development Server (http') && str_contains($data, 'started')) {
            $this->info('Server started on [http://' . $this->host() . ':' . $this->port() . ']');

            $this->writeLine('Press Ctrl+C to stop', 'fg=red');

            $this->newLine();
        }
    }
}
?>