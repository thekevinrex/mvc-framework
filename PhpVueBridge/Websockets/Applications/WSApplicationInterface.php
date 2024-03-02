<?php

namespace app\Core\Websockets\Applications;

use app\Core\Websockets\Connection;

interface WsApplicationInterface
{
    public function onConnected(Connection $connection): void;

    public function onDisconnected(Connection $connection): void;

    public function onData($data, Connection $connection): void;
}
?>