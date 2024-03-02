<?php

namespace app\Core\Websockets\Protocols;

class Rfc6455Protocol extends HybiProtocol
{

    const VERSION = 13;

    public function getVersion(): int
    {
        return self::VERSION;
    }

    public function acceptsVersion($version): bool
    {
        if ((int) $version <= 13) {
            return true;
        }
        return false;
    }
}
?>