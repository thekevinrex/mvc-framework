<?php

namespace app\Core\Websockets\Utils;

class Loop implements LoopInterface
{

    protected bool $loop = true;

    public function shouldContinue(): bool
    {
        return $this->loop;
    }

    public function while ($callback): void
    {
        if (!$callback instanceof \Closure) {
            throw new \InvalidArgumentException("Invalid While Callback, it must be a Closure Callback");
        }

        while ($this->shouldContinue()) {
            $callback();
        }
    }

    public function continue(bool $loop = true)
    {
        $this->loop = $loop;
    }
}
?>