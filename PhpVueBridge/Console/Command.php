<?php

namespace PhpVueBridge\Console;

use PhpVueBridge\Bedrock\Application;
use PhpVueBridge\Console\Interfaces\CommandInterface;
use PhpVueBridge\Console\Output\InteractWithConsole;


abstract class Command implements CommandInterface
{
    use InteractWithConsole;

    protected Application $app;

    protected Input $commands;

    public function __construct(Application $app, Input $commands)
    {
        $this->app = $app;
        $this->commands = $commands;
    }


}
?>