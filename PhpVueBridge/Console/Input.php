<?php
namespace PhpVueBridge\Console;

use PhpVueBridge\Http\Request\Request;


class Input
{

    protected Request $request;

    protected array $commands = [];

    protected string $filename;

    public function __construct(Request $request)
    {
        $this->request = $request;

        $commands = $request->server->get('argv');

        $this->filename = array_shift($commands);

        $this->parseCommands($commands);

    }

    protected function parseCommands(array $commands): void
    {

        $arg = [];
        $mod = [];

        foreach ($commands as $command) {

            $isCommand = true;

            if (str_starts_with($command, '-')) {

                $command = (str_starts_with($command, '--'))
                    ? substr($command, 2)
                    : substr($command, 1);

                $isCommand = false;
                $mod[] = $command;
            }

            if (str_contains($command, ':')) {

                [$command, $args] = explode(':', $command, 2);

                foreach (explode(',', $args) as $arg_name) {
                    $arg[] = $arg_name;
                }

            }

            if ($isCommand) {
                $this->commands[] = [
                    'mod' => $mod,
                    'arg' => $arg,
                    'command' => $command,
                ];

                $arg = [];
                $mod = [];
            }
        }
    }


    public function getFirstCommnad(): array
    {
        return reset($this->commands);
    }

    public function getFirstCommandName(): string
    {
        return $this->getFirstCommnad()['command'] ?? '';
    }

    public function getNextCommand(): array
    {
        return next($this->commands);
    }

    public function getCurrentCommand(): array
    {
        return current($this->commands);
    }


    public function getRequest(): Request
    {
        return $this->request;
    }

    public function count(): int
    {
        return count($this->commands);
    }
}
?>