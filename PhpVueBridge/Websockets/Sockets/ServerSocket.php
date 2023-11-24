<?php

namespace app\Core\Websockets\Sockets;

use app\Core\Websockets\Exceptions\SocketConnectionError;
use app\Core\Websockets\Server;
use app\Core\Websockets\Utils\UriValidator;

/**
 * It create the master socket for the server that is going to accept the new connections
 */
class ServerSocket extends UriSocket
{
    /**
     * If the master socket has been created and put to listen
     */
    protected bool $listening = false;

    /**
     * The error number in case that the master socket failed to create
     */
    protected $errno;

    /**
     * The error messagge in case that the master socket failed to create
     */
    protected $errstr;

    protected Server $server;


    public function __construct(Server $server, array $options = [])
    {
        $this->server = $server;

        $this->options = array_merge([
            'backlog' => 50,
            'ssl_cert_file' => null,
            'ssl_passphrase' => null,
            'ssl_allow_self_signed' => false,
            'timeout_accept' => 5,
        ], $options);

        [$this->scheme, $this->host, $this->port] = (new UriValidator($this->server->getUri()))->validateSocketUri();
    }

    /**
     * 
     * This create the master socket and puting to listeng
     */
    public function listen(): bool
    {
        if ($this->listening) {
            return true;
        }

        $this->socket = $this->createStreamSocket();

        if (!$this->socket) {
            throw new SocketConnectionError(
                sprintf(
                    'Could not listen on socket: %s (%d)',
                    $this->errstr,
                    $this->errno
                )
            );
        }

        return $this->listening = true;
    }

    /**
     * This accept a new socket
     */
    public function accept()
    {
        $new = stream_socket_accept(
            $this->socket,
            $this->options['timeout_accept']
        );

        if (!$new) {
            throw new SocketConnectionError(socket_strerror(socket_last_error($new)));
        }

        socket_set_option(socket_import_stream($new), SOL_TCP, TCP_NODELAY, 1);

        return $new;
    }

    /**
     * This create the master socket
     */
    protected function createStreamSocket()
    {
        return stream_socket_server(
            $this->getUri(),
            $this->errno,
            $this->errstr,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
            $this->getStreamContext()
        );
    }

    /**
     * if the master socket is listening
     */
    public function isListening(): bool
    {
        return $this->listening;
    }

    /**
     * Returns an array of socket stream context options
     * See http://php.net/manual/en/context.socket.php
     */
    protected function getSocketStreamContextOptions(): array
    {
        return [
            'backlog' => $this->options['backlog'] ?? 50,
        ];
    }

    /**
     * Returns an array of ssl stream context options
     * See http://php.net/manual/en/context.ssl.php
     */
    protected function getSslStreamContextOptions(): array
    {
        $options = [];

        // BC: use server_ssl_local_cert (old value: server_ssl_cert_file)
        if (!empty($this->options['server_ssl_cert_file'])) {
            $options['local_cert'] = $this->options['server_ssl_cert_file'];
        }

        // Otherwise map any options through
        foreach ($this->options as $option => $value) {
            if (preg_match('/^server_ssl_(.*)$/', $option, $matches)) {
                $options[$matches[1]] = $value;
            }
        }

        return $options;
    }
}
?>