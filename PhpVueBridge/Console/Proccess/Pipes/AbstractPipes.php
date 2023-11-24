<?php


namespace PhpVueBridge\Console\Proccess\Pipes;

abstract class AbstractPipes
{
    public array $pipes = [];

    public function unBlock()
    {
        foreach ($this->pipes as $key => $pipe) {
            @stream_set_blocking($pipe, false);
        }
    }

    public function write()
    {
        return [$this->pipes[0]];
    }

    abstract public function readPipes(): array;

    abstract protected function getDescriptors(): array;

    public function close()
    {
        foreach ($this->pipes as $pipe) {
            if (\is_resource($pipe)) {
                fclose($pipe);
            }
        }

        $this->pipes = [];
    }
}

?>