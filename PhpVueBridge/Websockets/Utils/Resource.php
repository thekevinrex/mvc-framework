<?php

namespace app\Core\Websockets\Utils;

use app\Core\Websockets\Sockets\Socket;

class Resource
{

    public static function getResourceId($socket): int
    {

        if ($socket instanceof Socket) {
            $socket = $socket->getResource();
        }

        return (int) $socket;
    }

    public static function getResource(Socket $socket)
    {
        return $socket->getResource();
    }
}
?>