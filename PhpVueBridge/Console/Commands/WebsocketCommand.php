<?php

namespace app\Core\Console\Commands;

use app\Core\Application;
use app\Core\Console\Command;
use app\Core\Console\Input;

class WebsocketCommand extends Command
{

    public function __construct(Application $app, Input $input)
    {
        parent::__construct($app, $input);
    }

    public function excecute()
    {
        $this->app->resolve('websocket_server')->createServer();
    }
}
?>