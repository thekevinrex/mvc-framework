<?php

namespace PhpVueBridge\Console;

use PhpVueBridge\Bedrock\Application;
use PhpVueBridge\Console\Contracts\ConsoleContract;


class Handler implements ConsoleContract
{

    protected Application $app;

    protected $aviableCommands = [
        'serve' => \PhpVueBridge\Console\Commands\ServeCommand::class,
        'migrate' => \app\Core\Console\Commands\MigrateCommand::class,
        'Websocket' => \app\Core\Console\Commands\WebsocketCommand::class,
    ];

    public function __construct(Application $app)
    {
        $this->app = $app;

        $app->setActualEnviroment('console');
    }

    public function excecuteCommand(Input $commands): void
    {
        $commandName = 'serve';

        if ($commands->count() > 0) {
            $firstCommand = $commands->getFirstCommnad();

            if (!in_array($firstCommand['command'], array_keys($this->aviableCommands))) {
                return;
            }

            $commandName = $firstCommand['command'];
        }

        $this->app->instance(Input::class, $commands);
        $this->app->instance('request', $commands->getRequest());

        // Bootstrap the app with the given bootstrapper
        $this->app->bootstrap([
            \PhpVueBridge\Bedrock\Bootstrapers\LoadEnvironmentVariables::class,
            \PhpVueBridge\Bedrock\Bootstrapers\RegisterFacades::class,
            \PhpVueBridge\Bedrock\Bootstrapers\LoadConfigFiles::class,
            \PhpVueBridge\Bedrock\Bootstrapers\HandleExceptions::class,
            \PhpVueBridge\Bedrock\Bootstrapers\RegisterProviders::class,
        ]);

        $command = $this->app->resolve($this->aviableCommands[$commandName]);

        if (!empty($firstCommand['arg'])) {
        } else {
            $command->excecute();
        }
    }
}
