<?php

namespace app\Core\Websockets\Sockets;

class ServerClientSocket extends Socket
{

    public function __construct($accepted_socket)
    {
        $this->socket = $accepted_socket;
        $this->connected = (boolean) $accepted_socket;
    }
}
?>