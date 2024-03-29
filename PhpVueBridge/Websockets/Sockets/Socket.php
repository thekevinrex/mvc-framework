<?php

namespace app\Core\Websockets\Sockets;

use app\Core\Websockets\Exceptions\SocketException;

/**
 * The socket is the resource for the connection between the server and the client
 */
abstract class Socket
{

    const TIMEOUT_SOCKET = 5;
    const DEFAULT_RECEIVE_LENGTH = '1400';
    const NAME_PART_IP = 0;
    const NAME_PART_PORT = 1;

    /**
     * The socket resource
     */
    protected $socket = null;

    /**
     * If the socket is connected
     */
    protected bool $connected = false;

    /**
     * The socket options
     */
    protected array $options;

    /**
     * The socket name
     */
    protected string $name;

    /**
     * Get the resource of the socket
     */
    public function getResource()
    {
        return $this->socket;
    }

    /**
     * Return if the socket is connected
     */
    public function connected(): bool
    {
        return $this->connected;
    }

    /**
     * Send data from the socket
     */
    public function send(string $data): ?int
    {
        if (!$this->connected) {
            throw new SocketException('Socket is not connected');
        }

        $length = strlen($data);

        if ($length == 0) {
            return 0;
        }

        for ($i = $length; $i > 0; $i -= $written) {
            $written = @fwrite($this->socket, substr($data, -1 * $i));

            if ($written === false) {
                return null;
            } elseif ($written === 0) {
                return null;
            }
        }

        return $length;
    }

    /**
     * Receive data from the socket
     *
     * @param int $length
     * @return string
     */
    public function receive(int $length = self::DEFAULT_RECEIVE_LENGTH): string
    {
        $buffer = '';
        $metadata['unread_bytes'] = 0;
        $makeBlockingAfterRead = false;

        try {
            do {
                // feof means socket has been closed
                // also, sometimes in long running processes the system seems to kill the underlying socket
                if (!$this->socket || feof($this->socket)) {
                    $this->disconnect();

                    return $buffer;
                }

                $result = fread($this->socket, $length);

                if ($makeBlockingAfterRead) {
                    stream_set_blocking($this->socket, true);
                    $makeBlockingAfterRead = false;
                }

                // fread FALSE means socket has been closed
                if ($result === false) {
                    $this->disconnect();

                    return $buffer;
                }

                $buffer .= $result;

                // feof means socket has been closed
                if (feof($this->socket)) {
                    $this->disconnect();

                    return $buffer;
                }

                $continue = false;

                if (strlen($result) == 1) {
                    // Workaround Chrome behavior (still needed?)
                    $continue = true;
                }

                if (strlen($result) == $length) {
                    $continue = true;
                }

                // Continue if more data to be read
                $metadata = stream_get_meta_data($this->socket);

                if ($metadata && isset($metadata['unread_bytes'])) {
                    if (!$metadata['unread_bytes']) {
                        // stop it, if we read a full message in previous time
                        $continue = false;
                    } else {
                        $continue = true;
                        // it makes sense only if unread_bytes less than DEFAULT_RECEIVE_LENGTH
                        if ($length > $metadata['unread_bytes']) {
                            // http://php.net/manual/en/function.stream-get-meta-data.php
                            // 'unread_bytes' don't describes real length correctly.
                            //$length = $metadata['unread_bytes'];

                            // Socket is a blocking by default. When we do a blocking read from an empty
                            // queue it will block and the server will hang. https://bugs.php.net/bug.php?id=1739
                            stream_set_blocking($this->socket, false);
                            $makeBlockingAfterRead = true;
                        }
                    }
                }
            } while ($continue);

            return $buffer;
        } finally {
            if ($this->socket && !feof($this->socket) && $makeBlockingAfterRead) {
                stream_set_blocking($this->socket, true);
            }
        }
    }

    /**
     * Disconnect a socket
     */
    public function disconnect(): void
    {
        if ($this->socket) {
            stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
        }
        $this->socket = null;
        $this->connected = false;
    }


    /**
     * Get the ip of the socket
     */
    public function getIp(): string
    {
        $name = $this->getName();

        if ($name) {
            return self::getNamePart($name, self::NAME_PART_IP);
        } else {
            throw new SocketException('Cannot get socket IP address');
        }
    }

    /**
     * Get the port of the socket
     */
    public function getPort(): int
    {
        $name = $this->getName();

        if ($name) {
            return (int) self::getNamePart($name, self::NAME_PART_PORT);
        } else {
            throw new SocketException('Cannot get socket IP address');
        }
    }

    /**
     * Get the name of the socket
     */
    protected function getName(): string
    {
        if (!isset($this->name) || !$this->name) {
            $this->name = @stream_socket_get_name($this->socket, true);
        }
        return $this->name;
    }

    /**
     * Slice the socket name to get the different part of the name (ip, port)
     */
    public static function getNamePart(string $name, int $part): string
    {
        if (!$name) {
            throw new \InvalidArgumentException('Invalid name');
        }

        $parts = explode(':', $name);

        if (count($parts) < 2) {
            throw new SocketException('Could not parse name parts: ' . $name);
        }

        if ($part == self::NAME_PART_PORT) {
            return end($parts);
        } elseif ($part == self::NAME_PART_IP) {
            return implode(':', array_slice($parts, 0, -1));
        } else {
            throw new \InvalidArgumentException('Invalid name part');
        }
    }

    /**
     * Get the last error of the socket if the socket is connected or set the error to not connected
     */
    public function getLastError(): string
    {
        if ($this->connected && $this->socket) {
            $err = @socket_last_error($this->socket);
            if ($err) {
                $err = socket_strerror($err);
            }
            if (!$err) {
                $err = 'Unknown error';
            }
            return $err;
        } else {
            return 'Not connected';
        }
    }
}
?>