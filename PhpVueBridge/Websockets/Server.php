<?php

namespace app\Core\Websockets;

use app\Core\Facades\Event;
use app\Core\Logger\LoggerInterface;
use app\Core\Logger\NullLogger;
use app\Core\Websockets\Applications\WsApplicationHandleUpdate;
use app\Core\Websockets\Applications\WsApplicationInterface;
use app\Core\Websockets\Protocols\Protocol;
use app\Core\Websockets\Protocols\Rfc6455Protocol;
use app\Core\Websockets\Utils\Loop;
use app\Core\Websockets\Utils\LoopInterface;

class Server
{

    /**
     * The uri in wich the server started
     */
    protected string $uri;

    /**
     * The loop used in the run method
     */
    protected LoopInterface $loop;

    /**
     * The connection manager is in charge of listening to new connections and create those connections
     */
    protected ConnectionManager $manager;

    /**
     * This is in charge of validating the requests and create the responses
     */
    protected Protocol $protocol;

    protected LoggerInterface $logger;

    protected array $apps = [];

    protected array $intances = [];

    public function __construct(string $uri, array $options = [])
    {
        $this->uri = $uri;
        $this->loop = $this->getDefaultLoop();
        $this->logger = new NullLogger;
    }

    /**
     * This is the start point of the websocket server, this mantening running and listen to all the events
     */
    public function run(): void
    {
        $this->getConnectionManager()->listen();

        if (!$this->getConnectionManager()->isListening()) {
            $this->notify(ServerFactory::EVENT_SERVER_FAILED);
        }

        $this->notify(ServerFactory::EVENT_SERVER_CREATED, [$this]);

        $this->loop->while(
            function () {

                $this->runTimers();

                $this->getConnectionManager()->selectAndProccess();

                foreach ($this->apps as $key => $app) {
                    if ($app instanceof WsApplicationHandleUpdate) {
                        $this->getApplication($key)->onUpdate();
                    }
                }
            }
        );
    }

    /**
     * This notify to the event listeners all the events
     */
    public function notify(string $event, array $data = []): void
    {
        Event::dispatch($event, $data);
    }

    public function addListener(string $event, $callback): void
    {
        Event::listen($event, $callback);
    }

    protected function runTimers()
    {
        # code...
        #TODO
    }

    public function registerApplication($name, WsApplicationInterface $app)
    {
        $this->apps[$name] = $app;
    }

    public function registerApplications(array $apps = [])
    {

        $apps = array_merge(
            [
                ...$apps,
                'status' => \app\Core\Websockets\Applications\StatusApplication::class,
                '' => \app\Core\Websockets\Applications\BaseApplication::class,
            ],
            config('websocket.applications', [])
        );

        $this->apps = $apps;
    }

    public function hasApplication(string $app): bool
    {
        return isset($this->apps[$app]);
    }

    public function getApplication(string $app): WsApplicationInterface
    {
        if (isset($this->intances[$app])) {
            return $this->intances[$app];
        }

        if (!$this->hasApplication($app)) {
            throw new \InvalidArgumentException('Application has not been registred : ' . $app);
        }

        return (is_object($this->apps[$app]))
            ? $this->apps[$app]
            : new $this->apps[$app];
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    protected function getConnectionManager(): ConnectionManager
    {
        return $this->manager ??= $this->getDefaultConnectionManager();
    }

    public function getProtocol(): Protocol
    {
        return $this->protocol ??= $this->getDefaultProtocol();
    }

    protected function getDefaultConnectionManager(): ConnectionManager
    {
        return new ConnectionManager($this);
    }

    protected function getDefaultLoop(): LoopInterface
    {
        return new Loop();
    }

    protected function getDefaultProtocol(): Protocol
    {
        return new Rfc6455Protocol();
    }
}
?>