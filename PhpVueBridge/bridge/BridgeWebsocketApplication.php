<?php

namespace PhpVueBridge\Bridge;


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