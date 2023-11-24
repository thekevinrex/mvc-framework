<?php


namespace app\Core\Websockets\Applications;

class BaseApplication extends WsApplication implements WsApplicationHandleClient
{

    public function onData($data, \app\Core\Websockets\Connection $connection): void
    {
        # code...
    }

    public function onConnected(\app\Core\Websockets\Connection $connection): void
    {
        # code...
    }

    public function onDisconnected(\app\Core\Websockets\Connection $connection): void
    {
        # code...
    }

    public function onClientData($data, \app\Core\Websockets\Connection $connection): void
    {

    }

}
?>