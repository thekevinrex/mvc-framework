<?php

namespace app\Core\Websockets\Sockets;

use app\Core\Websockets\Protocols\Protocol;

/**
 * It validate the uri in wich the server is going to be created and get the master socket context
 */
abstract class UriSocket extends Socket
{
    protected string $scheme;

    protected string $host;

    protected string $port;

    /**
     * Format the current uri
     */
    protected function getUri(): string
    {
        return sprintf(
            '%s://%s:%s',
            $this->scheme,
            $this->host,
            $this->port,
        );
    }

    protected function getName(): string
    {
        return sprintf('%s:%s', $this->host, $this->port);
    }

    /**
     * Create a master socket context
     */
    protected function getStreamContext()
    {
        $options = [];

        if (
            $this->scheme == Protocol::SCHEME_UNDERLYING_SECURE
            || $this->scheme == Protocol::SCHEME_UNDERLYING
        ) {
            $options['socket'] = $this->getSocketStreamContextOptions();
        }

        if ($this->scheme == Protocol::SCHEME_UNDERLYING_SECURE) {
            $options['ssl'] = $this->getSslStreamContextOptions();
        }

        return stream_context_create(
            $options,
            []
        );
    }

    abstract protected function getSocketStreamContextOptions(): array;

    abstract protected function getSslStreamContextOptions(): array;
}
?>