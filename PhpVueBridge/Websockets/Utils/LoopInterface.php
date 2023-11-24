<?php

namespace app\Core\Websockets\Utils;

interface LoopInterface
{
    public function shouldContinue(): bool;
    public function while ($callback): void;
}
?>