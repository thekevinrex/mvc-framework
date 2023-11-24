<?php

namespace app\Core\Websockets\Listeners;

use app\Core\Logger\LoggerInterface;
use app\Core\Websockets\Connection;
use app\Core\Websockets\Server;
use app\Core\Websockets\ServerFactory;

class RateLimiter implements RateLimiterInterface
{

    protected Server $server;

    protected array $options;

    protected array $ips = [];

    protected array $requests = [];

    public function __construct(
        protected LoggerInterface $logger,
        array $options = []
    ) {
        $this->options = array_merge(
            $options,
            [
                'max_connections' => 100,
                'max_connections_per_ip' => 5,
                'requests_per_minute' => 10
            ]
        );
    }

    public function listen(Server $server): RateLimiter
    {

        $this->server = $server;

        $server->addListener(
            ServerFactory::EVENT_SOCKET_CONNECT,
            [$this, 'handleSocketConect']
        );

        $server->addListener(
            ServerFactory::EVENT_SOCKET_DISCONNECT,
            [$this, 'handleSocketDisconect']
        );

        $this->server->addListener(
            ServerFactory::EVENT_SOCKET_DATA,
            [$this, 'handleSocketData']
        );

        return $this;
    }

    public function handleSocketConect($socket, $connection)
    {
        if ($this->checkMaxConnections($connection)) {
            $this->disconect($connection, 'Max connections');
        }

        $this->limitConnectionsPerIp($connection);
    }

    public function handleSocketDisconect($socket, $connection)
    {
        $this->handleDisconect($connection);
    }

    public function handleSocketData($socket, $connection)
    {
        $this->checkRequestsPerMinute($connection);
    }

    protected function checkMaxConnections($connection): bool
    {
        if ($connection->getManager()->count() > $this->options['max_connections']) {
            return true;
        }

        return false;
    }

    protected function limitConnectionsPerIp(Connection $connection)
    {

        $ip = $connection->getIp();

        if (!$ip) {
            $this->logger->warning('Cannot release connection');
            return;
        }

        $this->ips[$ip] = ($this->ips[$ip] ?? 0) + 1;

        if ($this->ips[$ip] > $this->options['max_connections_per_ip']) {
            $this->handleDisconect($connection);
            $this->disconect($connection, 'Max connections per ip');
        }
    }

    protected function checkRequestsPerMinute(Connection $connection)
    {
        $id = $connection->getId();

        if (!isset($this->requests[$id])) {
            $this->requests[$id] = [];
        }

        // Add current token
        $this->requests[$id][] = time();

        // Expire old tokens
        while (reset($this->requests[$id]) < time() - 60) {
            array_shift($this->requests[$id]);
        }

        if (count($this->requests[$id]) > $this->options['requests_per_minute']) {
            $this->handleDisconect($connection);
            $this->disconect($connection, 'Requests per minute');
        }
    }

    protected function handleDisconect(Connection $connection)
    {
        $ip = $connection->getIp();

        if (!$ip) {
            $this->logger->warning('Cannot release connection');
            return;
        }

        if (!isset($this->ips[$ip]) || $this->ips[$ip] == 0) {
            $this->ips[$ip] = 1;
        }

        $this->ips[$ip]--;

        unset($this->requests[$connection->getId()]);
    }

    protected function disconect(Connection $connection, $reason)
    {
        $this->logger->notice(
            sprintf(
                'Closing connection %s: %s',
                $connection->getIp(),
                $reason
            )
        );

        $connection->close();
    }
}
?>