<?php

namespace app\Core\Websockets\Sockets;

use app\Core\Websockets\Exceptions\SocketConnectionError;
use app\Core\Websockets\Utils\UriValidator;

class ClientSocket extends UriSocket
{
    /**
     * Default connection timeout
     *
     * @var int seconds
     */
    const TIMEOUT_CONNECT = 2;

    /**
     * The error number in case that the master socket failed to create
     */
    protected $errno;

    /**
     * The error messagge in case that the master socket failed to create
     */
    protected $errstr;

    public function __construct(string $uri, array $options = [])
    {

        $this->options = array_merge([
            'timeout_socket' => self::TIMEOUT_SOCKET,
            'timeout_connect' => self::TIMEOUT_CONNECT,
            'ssl_verify_peer' => false,
            'ssl_allow_self_signed' => true,
        ], $options);

        [$this->scheme, $this->host, $this->port] = (new UriValidator($uri))->validateSocketUri();
    }

    public function reconnect()
    {
        $this->disconnect();
        $this->connect();
    }

    /**
     * Connects to the given socket
     */
    public function connect(string $app): bool
    {
        if ($this->connected()) {
            return true;
        }

        // Supress PHP error, we're handling it
        $this->socket = @stream_socket_client(
            $this->getUri() . ('/' . $app ?? ''),
            $this->errno,
            $this->errstr,
            $this->options['timeout_connect'],
            STREAM_CLIENT_CONNECT,
            $this->getStreamContext()
        );

        if (!$this->socket) {
            throw new SocketConnectionError(
                sprintf(
                    'Could not connect on socket: %s (%d)',
                    $this->errstr,
                    $this->errno
                )
            );
        }

        stream_set_timeout($this->socket, $this->options['timeout_socket']);

        return $this->connected = true;
    }

    protected function getSocketStreamContextOptions(): array
    {
        return [];
    }

    protected function getSslStreamContextOptions(): array
    {
        $options = [];

        if ($this->options['ssl_verify_peer']) {
            $options['verify_peer'] = true;
        }

        if ($this->options['ssl_allow_self_signed']) {
            $options['allow_self_signed'] = true;
        }

        return $options;
    }
}

?>