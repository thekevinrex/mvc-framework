<?php

namespace app\core\bridge;

use app\Core\Websockets\Applications\WsApplication;
use app\Core\Websockets\Connection;

class BridgeWebsocketApplication extends WsApplication
{

    public function onData($data, Connection $connection): void
    {
        $connection->send($data);
    }

    public function onConnected(Connection $connection): void
    {
        # code...
    }

    public function onDisconnected(Connection $connection): void
    {
        # code...
    }
}
?>