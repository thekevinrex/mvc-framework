<?php

namespace app\Core\Websockets\Protocols;

use app\Core\Websockets\Exceptions\BadRequestException;
use app\Core\Websockets\Exceptions\HandshakeException;
use app\Core\Websockets\Utils\UriValidator;
use app\Core\Websockets\Utils\WSRequest;

abstract class Protocol
{

    public const SCHEME_WEBSOCKET = 'ws';
    public const SCHEME_WEBSOCKET_SECURE = 'wss';
    public const SCHEME_UNDERLYING = 'tcp';
    public const SCHEME_UNDERLYING_SECURE = 'tls';

    public const HEADER_HOST = 'host';
    public const HEADER_KEY = 'sec-websocket-key';
    public const HEADER_PROTOCOL = 'sec-websocket-protocol';
    public const HEADER_VERSION = 'sec-websocket-version';
    public const HEADER_ACCEPT = 'sec-websocket-accept';
    public const HEADER_EXTENSIONS = 'sec-websocket-extensions';
    public const HEADER_ORIGIN = 'origin';
    public const HEADER_CONNECTION = 'connection';
    public const HEADER_UPGRADE = 'upgrade';
    /**#@-*/

    /**#@+
     * HTTP error statuses
     *
     * @var int
     */
    public const HTTP_SWITCHING_PROTOCOLS = 101;
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_RATE_LIMITED = 420;
    public const HTTP_SERVER_ERROR = 500;
    public const HTTP_NOT_IMPLEMENTED = 501;
    /**#@-*/

    /**#@+
     * Close statuses
     *
     * @see http://tools.ietf.org/html/rfc6455#section-7.4
     * @var int
     */
    public const CLOSE_NORMAL = 1000;
    public const CLOSE_GOING_AWAY = 1001;
    public const CLOSE_PROTOCOL_ERROR = 1002;
    public const CLOSE_DATA_INVALID = 1003;
    public const CLOSE_RESERVED = 1004;
    public const CLOSE_RESERVED_NONE = 1005;
    public const CLOSE_RESERVED_ABNORM = 1006;
    public const CLOSE_DATA_INCONSISTENT = 1007;
    public const CLOSE_POLICY_VIOLATION = 1008;
    public const CLOSE_MESSAGE_TOO_BIG = 1009;
    public const CLOSE_EXTENSION_NEEDED = 1010;
    public const CLOSE_UNEXPECTED = 1011;
    public const CLOSE_RESERVED_TLS = 1015;
    /**#@-*/

    /**#@+
     * Frame types
     *  %x0 denotes a continuation frame
     *  %x1 denotes a text frame
     *  %x2 denotes a binary frame
     *  %x3-7 are reserved for further non-control frames
     *  %x8 denotes a connection close
     *  %x9 denotes a ping
     *  %xA denotes a pong
     *  %xB-F are reserved for further control frames
     *
     * @var int
     */
    public const TYPE_CONTINUATION = 0;
    public const TYPE_TEXT = 1;
    public const TYPE_BINARY = 2;
    public const TYPE_ICP = 3;
    public const TYPE_RESERVED_4 = 4;
    public const TYPE_RESERVED_5 = 5;
    public const TYPE_RESERVED_6 = 6;
    public const TYPE_RESERVED_7 = 7;
    public const TYPE_CLOSE = 8;
    public const TYPE_PING = 9;
    public const TYPE_PONG = 10;
    public const TYPE_RESERVED_11 = 11;
    public const TYPE_RESERVED_12 = 12;
    public const TYPE_RESERVED_13 = 13;
    public const TYPE_RESERVED_14 = 14;
    public const TYPE_RESERVED_15 = 15;
    /**#@-*/

    /**
     * Magic GUID
     * Used in the WebSocket accept header
     *
     * @var string
     */
    const MAGIC_GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    /**
     * The request MUST contain an |Upgrade| header field whose value
     *   MUST include the "websocket" keyword.
     */
    const UPGRADE_VALUE = 'websocket';

    /**
     * The request MUST contain a |Connection| header field whose value
     *   MUST include the "Upgrade" token.
     */
    const CONNECTION_VALUE = 'Upgrade';

    /**
     * Request line format
     *
     * @var string printf compatible, passed request path string
     */
    const REQUEST_LINE_FORMAT = 'GET %s HTTP/1.1';


    /**
     * Response line format
     *
     * @var string
     */
    const RESPONSE_LINE_FORMAT = 'HTTP/1.1 %d %s';

    /**
     * Header line format
     *
     * @var string printf compatible, passed header name and value
     */
    const HEADER_LINE_FORMAT = '%s: %s';
    /**
     * Close status codes
     *
     * @var array<int => string>
     */
    public const CLOSE_REASONS = [
        self::CLOSE_NORMAL => 'normal close',
        self::CLOSE_GOING_AWAY => 'going away',
        self::CLOSE_PROTOCOL_ERROR => 'protocol error',
        self::CLOSE_DATA_INVALID => 'data invalid',
        self::CLOSE_DATA_INCONSISTENT => 'data inconsistent',
        self::CLOSE_POLICY_VIOLATION => 'policy violation',
        self::CLOSE_MESSAGE_TOO_BIG => 'message too big',
        self::CLOSE_EXTENSION_NEEDED => 'extension needed',
        self::CLOSE_UNEXPECTED => 'unexpected error',
        self::CLOSE_RESERVED => null, // Don't use these!
        self::CLOSE_RESERVED_NONE => null,
        self::CLOSE_RESERVED_ABNORM => null,
        self::CLOSE_RESERVED_TLS => null,
    ];
    /**
     * Frame types
     *
     * @todo flip values and keys?
     * @var array<string => int>
     */
    public const FRAME_TYPES = [
        'continuation' => self::TYPE_CONTINUATION,
        'text' => self::TYPE_TEXT,
        'binary' => self::TYPE_BINARY,
        'close' => self::TYPE_CLOSE,
        'ping' => self::TYPE_PING,
        'pong' => self::TYPE_PONG,
        'icp' => self::TYPE_ICP,
    ];
    /**
     * HTTP errors
     *
     * @var array<int => string>
     */
    public const HTTP_RESPONSES = [
        self::HTTP_SWITCHING_PROTOCOLS => 'Switching Protocols',
        self::HTTP_BAD_REQUEST => 'Bad Request',
        self::HTTP_UNAUTHORIZED => 'Unauthorized',
        self::HTTP_FORBIDDEN => 'Forbidden',
        self::HTTP_NOT_FOUND => 'Not Found',
        self::HTTP_NOT_IMPLEMENTED => 'Not Implemented',
        self::HTTP_RATE_LIMITED => 'Enhance Your Calm',
    ];
    public const SCHEMES = [
        self::SCHEME_WEBSOCKET,
        self::SCHEME_WEBSOCKET_SECURE,
        self::SCHEME_UNDERLYING,
        self::SCHEME_UNDERLYING_SECURE,
    ];


    /**
     * Validate if is a correct request handshake and returns the different component of the request
     */
    public function validateRequestHandshake($request): ?array
    {
        if (!$request) {
            return null;
        }

        $request = new WSRequest($request);
        $headers = $request->getHeaders();

        if (empty($headers[self::HEADER_ORIGIN])) {
            throw new BadRequestException("No origin header");
        }

        if (
            !isset($headers[self::HEADER_UPGRADE]) ||
            strtolower($headers[self::HEADER_UPGRADE]) != self::UPGRADE_VALUE
        ) {
            throw new BadRequestException("Invalid Upgrade header");
        }

        if (
            !isset($headers[self::HEADER_CONNECTION]) ||
            stripos($headers[self::HEADER_CONNECTION], self::CONNECTION_VALUE) === false
        ) {
            throw new BadRequestException('Invalid connection header');
        }

        if (!isset($headers[self::HEADER_HOST])) {
            // @todo Validate host == listening socket? Or would that break
            //        TCP proxies?
            throw new BadRequestException('No host header');
        }

        if (!isset($headers[self::HEADER_VERSION])) {
            throw new BadRequestException('No version header received on handshake request');
        }

        if (!$this->acceptsVersion($headers[self::HEADER_VERSION])) {
            throw new BadRequestException('Unsupported version: ' . $headers[self::HEADER_VERSION]);
        }

        if (!isset($headers[self::HEADER_KEY])) {
            throw new BadRequestException('No key header received');
        }

        [$path, $query] = $request->validateRequestLine();

        if ($query) {
            parse_str($query, $query);
        }

        $key = trim($headers[self::HEADER_KEY]);
        $origin = $headers[self::HEADER_ORIGIN];

        $protocol = null;
        if (isset($headers[self::HEADER_PROTOCOL])) {
            $protocol = $headers[self::HEADER_PROTOCOL];
        }

        $extensions = [];
        if (!empty($headers[self::HEADER_EXTENSIONS])) {
            $extensions = $headers[self::HEADER_EXTENSIONS];
            if (is_scalar($extensions)) {
                $extensions = [$extensions];
            }
        }

        $extraHeaders = $headers;

        foreach ([self::HEADER_UPGRADE, self::HEADER_CONNECTION, self::HEADER_ORIGIN, self::HEADER_HOST, self::HEADER_VERSION, self::HEADER_KEY, self::HEADER_PROTOCOL, self::HEADER_EXTENSIONS] as $unsetHeader) {
            unset($extraHeaders[$unsetHeader]);
        }

        return [$path, $origin, $key, $extensions, $protocol, $extraHeaders, $query];
    }

    /**
     * Get a handshake response 
     */
    public function getResponseHandshake($key, array $headers = []): string
    {
        $headers = array_merge(
            $this->getSuccessResponseHeaders(
                $key
            ),
            $headers
        );

        return $this->getHttpResponse(self::HTTP_SWITCHING_PROTOCOLS, $headers);
    }

    /**
     * Gets a version number
     */
    abstract public function getVersion(): int;

    /**
     * Subclasses should implement this method and return a boolean to the given
     * version string, as to whether they would like to accept requests from
     * user agents that specify that version.
     */
    abstract public function acceptsVersion($version): bool;

    /**
     * Gets the default request headers
     */
    protected function getDefaultRequestHeaders($host, $key, $origin)
    {
        return [
            self::HEADER_HOST => $host,
            self::HEADER_UPGRADE => self::UPGRADE_VALUE,
            self::HEADER_CONNECTION => self::CONNECTION_VALUE,
            self::HEADER_KEY => $key,
            self::HEADER_ORIGIN => $origin,
            self::HEADER_VERSION => $this->getVersion(),
        ];
    }

    /**
     * Get the headers of a success response
     */
    protected function getSuccessResponseHeaders($key): array
    {
        return [
            self::HEADER_UPGRADE => self::UPGRADE_VALUE,
            self::HEADER_CONNECTION => self::CONNECTION_VALUE,
            self::HEADER_ACCEPT => $this->getAcceptValue($key),
        ];
    }

    protected function getAcceptValue($encoded_key)
    {
        return base64_encode(sha1($encoded_key . self::MAGIC_GUID, true));
    }

    /**
     * It create a response with the given headers
     */
    protected function getHttpResponse($status, array $headers = [])
    {
        if (array_key_exists($status, self::HTTP_RESPONSES)) {
            $response = self::HTTP_RESPONSES[$status];
        } else {
            $response = self::HTTP_RESPONSES[self::HTTP_NOT_IMPLEMENTED];
        }

        $handshake = [
            sprintf(self::RESPONSE_LINE_FORMAT, $status, $response),
        ];

        foreach ($headers as $name => $value) {
            $handshake[] = sprintf(self::HEADER_LINE_FORMAT, $name, $value);
        }

        return implode("\r\n", $handshake) . "\r\n\r\n";
    }

    /**
     * Get an error reponse
     */
    public function getResponseError($e, array $headers = [])
    {
        $code = false;

        if ($e instanceof Exception) {
            $code = $e->getCode();
        } elseif (is_numeric($e)) {
            $code = (int) $e;
        }

        if (!$code || $code < 400 || $code > 599) {
            $code = self::HTTP_SERVER_ERROR;
        }

        return $this->getHttpResponse($code, $headers);
    }

    public function getClosePayload($e, $masked = true)
    {
        $code = false;

        if ($e instanceof Exception) {
            $code = $e->getCode();
        } elseif (is_numeric($e)) {
            $code = (int) $e;
        }

        if (!$code || !key_exists($code, self::CLOSE_REASONS)) {
            $code = self::CLOSE_UNEXPECTED;
        }

        $body = pack('n', $code) . self::CLOSE_REASONS[$code];

        $payload = $this->getPayload();
        return $payload->encode($body, self::TYPE_CLOSE, $masked);
    }

    abstract public function getPayload();

    /**
     * Generates a key suitable for use in the protocol
     * This base implementation returns a 16-byte (128 bit) random key as a
     * binary string.
     */
    public function generateKey(): string
    {
        if (extension_loaded('openssl')) {
            $key = openssl_random_pseudo_bytes(16);
        } else {
            // SHA1 is 128 bit (= 16 bytes)
            $key = sha1(spl_object_hash($this) . mt_rand(0, PHP_INT_MAX) . uniqid('', true), true);
        }

        return base64_encode($key);
    }

    /**
     * Gets request handshake string
     *   The leading line from the client follows the Request-Line format.
     *   The leading line from the server follows the Status-Line format.  The
     *   Request-Line and Status-Line productions are defined in [RFC2616].
     *   An unordered set of header fields comes after the leading line in
     *   both cases.  The meaning of these header fields is specified in
     *   Section 4 of this document.  Additional header fields may also be
     *   present, such as cookies [RFC6265].  The format and parsing of
     *   headers is as defined in [RFC2616].
     *
     * @param string $uri    WebSocket URI, e.g. ws://example.org:8000/chat
     * @param string $key    16 byte binary string key
     * @param string $origin Origin of the request
     * @return string
     */
    public function getRequestHandshake(
        $uri,
        $key,
        $origin,
        array $headers = []
    ) {
        if (!$uri || !$key || !$origin) {
            throw new \InvalidArgumentException('You must supply a URI, key and origin');
        }

        [$scheme, $host, $port, $path, $query] = (new UriValidator($uri))->validateUri();

        if ($query) {
            $path .= '?' . $query;
        }

        if ($scheme == self::SCHEME_WEBSOCKET && $port == 80) {
            // do nothing
        } elseif ($scheme == self::SCHEME_WEBSOCKET_SECURE && $port == 443) {
            // do nothing
        } else {
            $host = $host . ':' . $port;
        }

        $handshake = [
            sprintf(self::REQUEST_LINE_FORMAT, $path),
        ];

        $headers = array_merge(
            $this->getDefaultRequestHeaders(
                $host,
                $key,
                $origin
            ),
            $headers
        );

        foreach ($headers as $name => $value) {
            $handshake[] = sprintf(self::HEADER_LINE_FORMAT, $name, $value);
        }
        return implode("\r\n", $handshake) . "\r\n\r\n";
    }

    public function validateResponseHandshake($response, $key)
    {
        if (!$response) {
            return false;
        }

        $headers = (new WSRequest($response))->getHeaders();

        if (!isset($headers[self::HEADER_ACCEPT])) {
            throw new HandshakeException('No accept header receieved on handshake response');
        }

        $accept = $headers[self::HEADER_ACCEPT];

        if (!$accept) {
            throw new HandshakeException('Invalid accept header');
        }

        $expected = $this->getAcceptValue($key);

        preg_match('#Sec-WebSocket-Accept:\s(.*)$#imU', $response, $matches);
        $keyAccept = trim($matches[1]);

        return $keyAccept === $this->getEncodedHash($key);
    }

    public function getEncodedHash($key)
    {
        return base64_encode(pack('H*', sha1($key . self::MAGIC_GUID)));
    }
}
?>