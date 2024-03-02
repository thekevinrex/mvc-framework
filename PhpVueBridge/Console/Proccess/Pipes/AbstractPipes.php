<?php


namespace PhpVueBridge\Console\Proccess\Pipes;

abstract class AbstractPipes
{
    public array $pipes = [];

    public function unBlock()
    {
        // This set each pipe unblocked
        foreach ($this->pipes as $key => $pipe) {
            @stream_set_blocking($pipe, false);
        }
    }

    public function write()
    {
        return [$this->pipes[0]];
    }

    abstract public function readPipes(): array;

    protected function getDescriptors(): array
    {
        return [
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        ];
    }

    public function close()
    {
        // Iterate through each pipe and close them
        foreach ($this->pipes as $pipe) {
            if (\is_resource($pipe)) {
                fclose($pipe);
            }
        }

        // Set the pipes array to empty
        $this->pipes = [];
    }
}

?>