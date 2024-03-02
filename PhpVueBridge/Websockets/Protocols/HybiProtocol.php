<?php

namespace app\Core\Websockets\Protocols;

use app\Core\Websockets\Payload\HybiPayload;
use app\Core\Websockets\Payload\Payload;

abstract class HybiProtocol extends Protocol
{
    public function getPayload(): Payload
    {
        return new HybiPayload();
    }
}
?>