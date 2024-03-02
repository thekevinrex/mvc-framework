<?php

namespace PhpVueBridge\Bridge\Utils;

class Vite
{

    const PREFIX_FORMAT = "%s://%s:%s/";

    protected string $path;

    protected bool $isDev = false;

    protected array $server;

    protected bool $loaded = false;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    protected function loadServer()
    {
        if (!file_exists($this->path)) {
            return $this->loaded = false;
        }

        $this->isDev = true;
        $this->server = json_decode(file_get_contents($this->path), true);


        return $this->loaded = true;
    }

    public function formatFile(string $file): string
    {
        return $this->getPrefixUrl() . $file;
    }

    protected function getPrefixUrl()
    {
        if (!$this->loaded) {
            $this->loadServer();
        }

        $sheme = 'http';

        $host = ($this->server['family'] === 'IPv6' || $this->server['family'] === 6)
            ? '[' . $this->server['address'] . ']'
            : $this->server['address'];

        $port = $this->server['port'];


        return sprintf(
            self::PREFIX_FORMAT,
            $sheme,
            $host,
            $port
        );
    }

}

?>