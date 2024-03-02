<?php

namespace app\Core\Websockets;

use app\Core\Websockets\Exceptions\FrameException;
use app\Core\Websockets\Payload\Payload;
use app\Core\Websockets\Payload\PayloadHandler;
use app\Core\Websockets\Protocols\Protocol;
use app\Core\Websockets\Protocols\Rfc6455Protocol;
use app\Core\Websockets\Sockets\ClientSocket;
use app\Core\Websockets\Sockets\Socket;

class Client
{
    const MAX_HANDSHAKE_RESPONSE = '1500';

    protected string $uri;

    protected string $origin;

    protected Socket $socket;

    protected Protocol $protocol;

    protected PayloadHandler $payloadHandler;

    protected array $received;

    protected array $headers = [];

    protected bool $connected = false;

    protected \Closure $callback;

    public function __construct(
        string $uri,
        string $origin,

    ) {
        $this->uri = $uri;
        $this->origin = $origin;

        $this->configurePayloadHandler();
    }

    protected function configurePayloadHandler()
    {
        $this->payloadHandler = new PayloadHandler(
            $this->getProtocol(),
            [$this, 'onData']
        );
    }

    public function onData(Payload $payload)
    {
        $this->received[] = $payload;

        if (isset($this->callback) && $this->callback instanceof \Closure) {
            call_user_func($this->callback, (string) $payload);
        }
    }

    public function setCallback(\Closure $callback)
    {
        $this->callback = $callback;
    }

    public function addRequestHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    /**
     * Sends data to the socket
     */
    public function sendData(string $data, int $type = Protocol::TYPE_ICP, $masked = true): bool
    {
        if (!$this->connected()) {
            return false;
        }

        $payload = $this->getProtocol()->getPayload();

        $payload->encode(
            $data,
            $type,
            $masked
        );

        return $payload->sendToSocket($this->socket);
    }

    public function sendDataToApplication(string $data, int $type = Protocol::TYPE_TEXT, $masked = true): bool
    {
        return $this->sendData($data, $type, $masked);
    }

    public function receive(?\Closure $callback): ?array
    {
        if (!$this->connected()) {
            return null;
        }

        $data = $this->getSocket()->receive();

        if (!$data) {
            return [];
        }

        if ($callback) {
            $this->setCallback($callback);
        }

        $this->payloadHandler->handle($data);

        $received = $this->received;
        $this->received = [];
        return $received;
    }

    /**
     * Connect to the server
     */
    public function connect(string $app = ''): bool
    {
        if ($this->connected()) {
            return false;
        }

        try {
            $this->getSocket()->connect($app);
        } catch (\Exception $ex) {
            return false;
        }

        $key = $this->getProtocol()->generateKey();
        $handshake = $this->getProtocol()->getRequestHandshake(
            $this->uri . ('/' . $app ?? ''),
            $key,
            $this->origin,
            $this->headers
        );

        $this->getSocket()->send($handshake);

        $response = $this->getSocket()->receive(self::MAX_HANDSHAKE_RESPONSE);

        return ($this->connected =
            $this->getProtocol()->validateResponseHandshake($response, $key));
    }

    public function disconnect($reason = Protocol::CLOSE_NORMAL): bool
    {
        if (!$this->connected()) {
            return false;
        }

        $payload = $this->getProtocol()->getClosePayload($reason);

        if ($this->socket) {
            if (!$payload->sendToSocket($this->socket)) {
                throw new FrameException("Unexpected exception when sending Close frame.");
            }
            // The client SHOULD wait for the server to close the connection
            $this->getSocket()->receive();
            $this->getSocket()->disconnect();
        }

        $this->connected = false;

        return true;
    }

    public function getSocket(): Socket
    {
        return $this->socket ??= (new ClientSocket($this->uri));
    }

    public function getProtocol(): Protocol
    {
        return $this->protocol ??= $this->getDefaultProtocol();
    }
    protected function getDefaultProtocol(): Protocol
    {
        return new Rfc6455Protocol();
    }

    public function connected(): bool
    {
        return $this->connected = $this->getSocket()->connected() ?? false;
    }
}
?>