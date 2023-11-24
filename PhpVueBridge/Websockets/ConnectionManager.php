<?php

namespace app\Core\Websockets;

use app\Core\Websockets\Applications\WsApplicationInterface;
use app\Core\Websockets\Protocols\Protocol;
use app\Core\Websockets\Sockets\ServerClientSocket;
use app\Core\Websockets\Sockets\ServerSocket;
use app\Core\Websockets\Sockets\Socket;
use app\Core\Websockets\Utils\Resource;

class ConnectionManager implements \Countable
{

    public static $TIMEOUT_SELECT = 0;
    public static $TIMEOUT_SELECT_MICROSEC = 200000;

    /**
     * This is the master socket that is in charge of accepting new connections
     */
    protected Socket $socket;

    /**
     * The sockets that are current listening
     */
    protected array $resources;

    /**
     * This keep all the currents connections
     */
    protected array $connections;

    public function __construct(
        protected Server $server
    ) {

    }

    public function count(): int
    {
        return count($this->connections);
    }

    public function getApplicationForPath(string $path): ?WsApplicationInterface
    {
        $app = ltrim($path, '/');

        if ($this->getServer()->hasApplication($app)) {
            return $this->getServer()->getApplication($app);
        }

        return null;
    }

    /**
     * This set the master socket to listen, it create the master socket and it is put to listen 
     */
    public function listen(): void
    {
        if ($this->getSocket()->listen()) {
            $this->addResource(Resource::getResource($this->getSocket()));
        }
    }

    /**
     * This is listening and selecting the new events and proccessing those
     */
    public function selectAndProccess()
    {
        $read = $this->getResources();
        $unused_write = null;
        $unsued_exception = null;

        stream_select(
            $read,
            $unused_write,
            $unused_exception,
            static::$TIMEOUT_SELECT,
            static::$TIMEOUT_SELECT_MICROSEC,
        );

        foreach ($read as $socket) {
            if ($socket == $this->socket->getResource()) {
                $this->processMasterSocket();
            } else {
                $this->processClientSocket($socket);
            }
        }
    }

    /**
     * This proccess a new event for a given socket
     */
    protected function processClientSocket($socket)
    {
        $connection = $this->getConnectionForClientSocket($socket);

        if (!$connection) {
            $this->getServer()->getLogger()->warning(
                "No connection for client socket"
            );
            return;
        }

        try {

            $this->server->notify(ServerFactory::EVENT_SOCKET_DATA, [$socket, $connection]);

            if (!$connection->getSocket()->connected()) {
                return;
            }

            $connection->process();

        } catch (\Throwable $e) {
            $this->getServer()->getLogger()->warning(
                "Error on client socket: {$e}"
            );
            $connection->close();
        }
    }

    /**
     * This get the connection for the given socket
     */
    protected function getConnectionForClientSocket($socket): ?Connection
    {
        $socketId = Resource::getResourceId($socket);
        if (!isset($this->connections[$socketId])) {
            return null;
        }

        return $this->connections[$socketId];
    }

    /**
     * This proccess the new connections, the master socket accept a new socket and create a new connection
     */
    protected function processMasterSocket()
    {
        try {
            $new = $this->getSocket()->accept();
        } catch (\Throwable $e) {
            $this->getServer()->getLogger()->error(
                "Socket error: {$e}",
            );
            return;
        }

        $connection = $this->createConnection($new);
        $this->getServer()->notify(ServerFactory::EVENT_SOCKET_CONNECT, [$new, $connection]);
    }

    /**
     * This create a new connection from a given new socket and it is stored
     */
    protected function createConnection($resource): Connection
    {
        if (!$resource || !is_resource($resource)) {
            throw new \InvalidArgumentException('Invalid connection resource');
        }

        $socket = $this->getDefaultClientSocket($resource);
        $connection = $this->makeConnection($socket);

        $this->addResource($resource);
        $this->addConnection($resource, $connection);

        return $connection;
    }

    /**
     * This make a new connection object for the given socket
     */
    protected function makeConnection(ServerClientSocket $socket): Connection
    {
        return new Connection($socket, $this);
    }

    /**
     * It removes a connection from the stored connections and resources
     */
    public function removeConnection(Connection $connection): void
    {
        $socket = $connection->getSocket();

        if ($socket->getResource()) {
            $index = Resource::getResourceId($socket);
        } else {
            $index = array_search($connection, $this->connections);
        }

        if (!$index) {
            $this->getServer()->getLogger()->warning(
                "Could not remove connection: not found"
            );
        }

        unset($this->connections[$index]);
        unset($this->resources[$index]);

        $this->server->notify(
            ServerFactory::EVENT_SOCKET_DISCONNECT,
            [$connection->getSocket(), $connection]
        );
    }

    /**
     * This store a new socket
     */
    protected function addResource($socket): void
    {
        $this->resources[Resource::getResourceId($socket)] = $socket;
    }

    /**
     * This store a new connection
     */
    protected function addConnection($socket, Connection $connection)
    {
        $this->connections[Resource::getResourceId($socket)] = $connection;
    }

    /**
     * This get all the current socket that are listening
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * The socket client class for the new accepted socket
     */
    protected function getDefaultClientSocket($resource): ServerClientSocket
    {
        return new ServerClientSocket($resource);
    }

    /**
     * This get the master socket and if not yet instanced it create a new master socket object
     */
    public function getSocket(): Socket
    {
        return $this->socket ??= $this->getDefaultSocket();
    }

    /**
     * The default server socket class for the master socket
     */
    protected function getDefaultSocket(): Socket
    {
        return new ServerSocket($this->server, []);
    }

    /**
     * If the master socket is listening
     */
    public function isListening(): bool
    {
        return $this->getSocket()->isListening();
    }

    /**
     * This get the current server
     */
    public function getServer(): Server
    {
        return $this->server;
    }

    /** 
     * This get the server protocol from the current server
     */
    public function getServerProtocol(): Protocol
    {
        return $this->getServer()->getProtocol();
    }
}
?>