<?php

namespace PhpVueBridge\Logger;

class NullLogger extends Logger
{
    public function log($level, string $message, array $context = [])
    {
        # code...
    }
}
?>