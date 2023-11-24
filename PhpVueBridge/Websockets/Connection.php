<?php

namespace app\Core\Websockets;

use app\Core\Websockets\Applications\WsApplicationHandleClient;
use app\Core\Websockets\Applications\WsApplicationInterface;
use app\Core\Websockets\Exceptions\BadRequestException;
use app\Core\Websockets\Exceptions\CloseException;
use app\Core\Websockets\Exceptions\HandshakeException;
use app\Core\Websockets\Exceptions\SocketConnectionError;
use app\Core\Websockets\Payload\Payload;
use app\Core\Websockets\Payload\PayloadHandler;
use app\Core\Websockets\Protocols\Protocol;
use app\Core\Websockets\Sockets\ServerClientSocket;
use app\Core\Websockets\Sockets\Socket;

/**
 * A connection is a class that connect the server with the client this is in charge of proccessing the new event and sending events to the client, is the connection class that controle the socket that unite the server with the client
 */
class Connection
{

    /**
     * The ip for the current socket
     */
    protected string $ip;

    /**
     * The port for the current socket
     */
    protected int $port;

    /**
     * A unique and random id for the connection
     */
    protected string $id;

    /**
     * if the connection has performed a handshake
     */
    protected bool $handShaked = false;

    /**
     * The connection headers
     */
    protected $headers;

    /**
     * The connection query params
     */
    protected $query;

    /**
     * The payload handled class
     */
    protected PayloadHandler $payloadHandler;

    protected ?WsApplicationInterface $application = null;

    public function __construct(
        protected ServerClientSocket $socket,
        protected ConnectionManager $manager
    ) {
        $this->generateClientId();
        $this->configurePayloadHandler();
    }

    /**
     * this generate a random id for the connection
     */
    protected function generateClientId(): void
    {
        $this->id = bin2hex(random_bytes(32));
    }

    /**
     * This configure the payload handler
     */
    protected function configurePayloadHandler()
    {
        $this->payloadHandler = new PayloadHandler(
            $this->manager->getServerProtocol(),
            [$this, 'handlePayload'],
        );
    }

    public function handlePayload(Payload $payload): void
    {
        $this->manager->getServer()->getLogger()->debug('Handling payload from: ' . $this->getId());

        switch ($type = $payload->getType()) {
            case Protocol::TYPE_TEXT:
                if (method_exists($this->application, 'onData')) {
                    $this->application->onData((string) $payload, $this);
                }
                return;

            case Protocol::TYPE_BINARY:
                if (method_exists($this->application, 'onBinaryData')) {
                    $this->application->onBinaryData((string) $payload, $this);
                } else {
                    $this->close(1003);
                }
                break;

            case Protocol::TYPE_ICP:
                if ($this->application instanceof WsApplicationHandleClient) {
                    $this->application->onClientData((string) $payload, $this);
                }
                break;

            case Protocol::TYPE_PING:
                $this->manager->getServer()->getLogger()->notice('Ping received');
                $this->send($payload->getPayload(), Protocol::TYPE_PONG);
                $this->manager->getServer()->getLogger()->debug('Pong!');
                break;

            /**
             * A Pong frame MAY be sent unsolicited.  This serves as a
             * unidirectional heartbeat.  A response to an unsolicited Pong
             * frame is not expected.
             */
            case Protocol::TYPE_PONG:
                $this->manager->getServer()->getLogger()->info('Received unsolicited pong');
                break;

            case Protocol::TYPE_CLOSE:
                $this->close();
                break;

            default:
                throw new SocketConnectionError('Unhandled payload type');
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getIp(): string
    {
        return $this->ip ??= $this->getSocket()->getIp();
    }

    public function getPort(): int
    {
        return $this->port ??= $this->getSocket()->getPort();
    }

    public function close(int $code = Protocol::CLOSE_NORMAL, string $reason = null): bool
    {
        try {
            if (!$this->handShaked) {
                $response = $this->manager->getServerProtocol()->getResponseError($code);
                $this->getSocket()->send($response);
            } else {
                $response = $this->manager->getServerProtocol()->getClosePayload($code, false);
                $response->sendToSocket($this->socket);
            }
        } catch (\Throwable $e) {
            $this->manager->getServer()->getLogger()->warning('Unable to send close message');
        }

        if (isset($this->application) && method_exists($this->application, 'onDisconnected')) {
            $this->application->onDisconnected($this);
        }

        $this->getSocket()->disconnect();
        $this->manager->removeConnection($this);

        return true;
    }

    /**
     * Sends the payload to the connection
     *
     * @param string|Payload|mixed $data
     * @param int                  $type
     * @return bool
     */
    public function send(string $data, int $type = Protocol::TYPE_TEXT): bool
    {
        if (!$this->handShaked) {
            throw new HandshakeException('Connection is not handshaked');
        }

        $payload = $this->manager->getServerProtocol()->getPayload();

        if (!is_scalar($data) && !$data instanceof Payload) {
            $data = json_encode($data);
        }

        // Servers don't send masked payloads
        $payload->encode($data, $type, false);

        if (!$payload->sendToSocket($this->socket)) {
            $this->manager->getServer()->getLogger()->warning('Could not send payload to client');
            throw new SocketConnectionError('Could not send data to connection: ' . $this->getSocket()->getLastError());
        }

        return true;
    }

    /**
     * It proccess a event data from the socket
     */
    public function process()
    {
        // This recive the data from the socket
        $data = $this->getSocket()->receive();

        // Get the length to verify if there is data
        $bytes = strlen($data);

        if ($bytes === 0 || $data === false) {
            throw new CloseException('Error reading data from socket: ' . $this->socket->getLastError());
        }

        $this->onData($data);
    }

    /**
     * Verify if the connection has handshaked or handle the event data
     */
    protected function onData($data)
    {
        if (!$this->handShaked) {
            return $this->handShake($data);
        }

        return $this->handle($data);
    }

    /**
     * This handle the event data, it pass the data to the payload handler
     */
    public function handle($data)
    {
        $this->payloadHandler->handle($data);
    }

    /**
     * It handshake the connection
     */
    protected function handShake($data)
    {
        try {

            // Split the data to get the different components
            [
                $path,
                $origin,
                $key,
                $extensions,
                $protocol,
                $this->headers,
                $this->query
            ] = $this->manager->getServerProtocol()->validateRequestHandshake($data);

            $this->setApplication($path);

            $this->manager->getServer()->notify(
                ServerFactory::EVENT_HANDSHAKE_REQUEST,
                [$this, $path, $origin, $key, $extensions]
            );

            $response = $this->manager->getServerProtocol()->getResponseHandshake($key);

            if (!$this->getSocket()->connected()) {
                throw new HandshakeException('Socket is not connected');
            }

            if ($this->getSocket()->send($response) === null) {
                throw new HandshakeException('Could not send handshake response');
            }

            $this->handShaked = true;

            $this->manager->getServer()->getLogger()->info(
                sprintf(
                    'Handshake successful: %s:%d (%s) connected to %s',
                    $this->getIp(),
                    $this->getPort(),
                    $this->getId(),
                    $path
                )
            );

            $this->manager->getServer()->notify(
                ServerFactory::EVENT_HANDSHAKE_SUCCESSFUL,
                [$this]
            );

            if (method_exists($this->application, 'onConnected')) {
                $this->application->onConnected($this);
            }

        } catch (\Throwable $e) {
            $this->manager->getServer()->getLogger()->error(
                "Handshake failed: {$e}"
            );
            $this->close(Protocol::CLOSE_PROTOCOL_ERROR, (string) $e);
            throw $e;
        }
    }

    protected function setApplication(string $path)
    {
        $this->application = $this->manager->getApplicationForPath($path);

        if (!$this->application) {
            throw new BadRequestException("Invalid Application: " . $path);
        }
    }

    public function getApplication(): WsApplicationInterface
    {
        return $this->application;
    }

    public function getSocket(): Socket
    {
        return $this->socket;
    }

    public function getManager(): ConnectionManager
    {
        return $this->manager;
    }
}
?>