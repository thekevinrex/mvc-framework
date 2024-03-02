<?php

namespace app\Core\Websockets\Payload;

use app\Core\Websockets\Frame\Frame;
use app\Core\Websockets\Frame\HybiFrame;

/**
 * Gets a HyBi payload
 */
class HybiPayload extends Payload
{
    protected function getFrame(): Frame
    {
        return new HybiFrame();
    }
}