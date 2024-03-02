<?php

namespace app\App\Events;

use app\Core\Websockets\Server;

class TestingEvents
{

    public Server $server;

    public function __construct(Server $server)
    {
        $this->server = $server;
    }
}
?>