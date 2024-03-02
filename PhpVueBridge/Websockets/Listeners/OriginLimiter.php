<?php

namespace app\Core\Websockets\Listeners;

use app\Core\Logger\LoggerInterface;
use app\Core\Websockets\Connection;
use app\Core\Websockets\Server;

class OriginLimiter implements OriginLimiterInterface
{

    protected array $options;

    public function __construct(
        protected LoggerInterface $logger,
        array $options = []
    ) {
        $this->options = array_merge(
            $options,
            [
                'allowed_origins' => []
            ]
        );
    }

    public function listen(Server $server): OriginLimiter
    {

        $this->server = $server;

        $server->addListener(
            ServerFactory::EVENT_HANDSHAKE_REQUEST,
            [$this, 'checkHandshakeOrigin']
        );

        return $this;
    }

    public function checkHandshakeOrigin(
        Connection $connection,
        string $path,
        string $origin,
        string $key,
        array $extensions
    ): void {
        if (!$this->isAllowed($origin)) {
            $connection->close(Protocol::CLOSE_NORMAL, 'Not allowed origin during handshake request');
        }
    }

    protected function isAllowed(string $origin): bool
    {

        if (empty($this->options['allowed_origins'])) {
            return true;
        }

        $url = parse_url($origin);

        [$sheme, $host] = [
            $url['sheme'] ?? '',
            $url['host'] ?? $origin
        ];

        foreach ($this->options['allowed_origins'] as $allowed) {

            $allowed_url = parse_url($allowed);

            [$allowed_sheme, $allowed_host] = [
                $allowed_url['sheme'] ?? '',
                $allowed_url['host'] ?? $allowed
            ];

            if ($sheme == $allowed_sheme && $allowed_host == $host) {
                return true;
            }
        }

        return false;
    }
}
?>