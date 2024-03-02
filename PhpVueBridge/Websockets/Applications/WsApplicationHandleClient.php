<?php

namespace app\Core\Websockets\Applications;

use app\Core\Websockets\Connection;

interface WsApplicationHandleClient
{
    /**
     * Handle incoming data from internals applications of the backend
     */
    public function onClientData($data, Connection $connection): void;
}
?>