<?php

namespace app\Core\Websockets;

use app\App\Events\TestingEvents;
use app\App\Listeners\TestingListeners;
use app\Core\Application;
use app\Core\Facades\Event;
use app\Core\Logger\LoggerInterface;
use app\Core\Websockets\Listeners\OriginLimiter;
use app\Core\Websockets\Listeners\OriginLimiterInterface;
use app\Core\Websockets\Listeners\RateLimiter;
use app\Core\Websockets\Listeners\RateLimiterInterface;

class ServerFactory
{

    const EVENT_SERVER_CREATED = 'server_created';

    const EVENT_SERVER_FAILED = 'server_failed';

    const EVENT_SOCKET_CONNECT = 'socket_connect';

    const EVENT_SOCKET_DISCONNECT = 'socket_disconected';

    const EVENT_SOCKET_DATA = 'event_data';

    const EVENT_HANDSHAKE_REQUEST = 'handshake_request';

    const EVENT_HANDSHAKE_SUCCESSFUL = 'handshake_successful';


    protected RateLimiterInterface $limiter;

    protected OriginLimiterInterface $origin;

    protected Server $server;

    protected array $apps = [];

    protected array $listeners = [];

    public function __construct(
        protected LoggerInterface $logger,
    ) {

    }

    public function create(string $uri, array $options = []): ServerFactory
    {
        $this->server = (new Server($uri, $options))
            ->setLogger($this->logger);

        return $this;
    }

    public function createServer(): void
    {
        $this->create(
            config('websocket.server'),
        );

        if (config('websocket.rate_limiter', false)) {
            $this->withRateLimiter(
                $this->getDefaultRateLimiter(
                    config('websocket.rate_limiter_options')
                )
            );
        }

        if (config('websocket.origin_limiter', false)) {
            $this->withOriginLimiter(
                $this->getDefaultOriginLimiter(
                    config('websocket.origin_limiter_options')
                )
            );
        }

        $this->run();
    }

    public function run(): void
    {

        if (!$this->server) {
            throw new \LogicException('Before run the server, you need to create first');
        }

        $this->server->registerApplications(
            $this->apps
        );

        if (isset($this->limiter)) {
            $this->limiter->listen($this->server);
        }

        if (isset($this->origin)) {
            $this->limiter->listen($this->server);
        }

        foreach ($this->listeners as $key => $listener) {
            $this->server->addListener($key, $listener);
        }

        $this->server->run();
    }

    public function setLogger(LoggerInterface $logger): ServerFactory
    {
        $this->logger = $logger;

        if (isset($this->server)) {
            $this->server->setLogger($logger);
        }

        return $this;
    }

    public function withRateLimiter(RateLimiterInterface $limiter = null): ServerFactory
    {
        $this->limiter = (is_null($limiter))
            ? $this->getDefaultRateLimiter()
            : $limiter;

        return $this;
    }

    public function withOriginLimiter(OriginLimiterInterface $origin = null): ServerFactory
    {
        $this->origin = (is_null($origin))
            ? $this->getDefaultOriginLimiter()
            : $origin;

        return $this;
    }

    protected function getDefaultRateLimiter(array $options = []): RateLimiterInterface
    {
        return new RateLimiter($this->logger, $options);
    }

    protected function getDefaultOriginLimiter(array $options = []): OriginLimiterInterface
    {
        return new OriginLimiter($this->logger, $options);
    }

    public function registerApplication($key, $app): void
    {
        $this->apps[$key] = $app;
    }

    public function registerListener($key, $listener): void
    {
        $this->listeners[$key] = $listener;
    }
}

?>