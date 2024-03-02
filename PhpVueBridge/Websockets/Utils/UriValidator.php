<?php

namespace app\Core\Websockets\Utils;

use app\Core\Websockets\Protocols\Protocol;
use app\Core\Websockets\Server;

class UriValidator
{

    public function __construct(protected string $uri)
    {

    }

    public function validateSocketUri(): array
    {
        if (!$this->uri) {
            throw new \InvalidArgumentException('Invalid Uri');
        }

        $url = parse_url($this->uri);

        if (!$url || !is_array($url) || count($url) < 3) {
            throw new \InvalidArgumentException('Invalid Uri');
        }

        [$schema, $host, $port] = [$url['scheme'] ?? null, $url['host'] ?? null, $url['port'] ?? null];

        return [
            $this->validateSchema($schema),
            $this->validateHost($host),
            $this->validatePort($port, $schema)
        ];
    }

    public function validateUri()
    {
        if (!$this->uri) {
            throw new \InvalidArgumentException('Invalid Uri');
        }

        $url = parse_url($this->uri);

        if (!$url || !is_array($url) || count($url) < 3) {
            throw new \InvalidArgumentException('Invalid Uri');
        }

        [
            $scheme,
            $host,
            $port,
            $path,
            $query
        ] = [
            $url['scheme'] ?? null,
            $url['host'] ?? null,
            $url['port'] ?? null,
            $url['path'] ?? null,
            $url['query'] ?? null,
        ];

        if (!$path) {
            throw new \InvalidArgumentException('Invalid path');
        }

        return [
            $this->validateSchema($scheme),
            $this->validateHost($host),
            $this->validatePort($port, $scheme),
            $path,
            $query
        ];
    }

    protected function validateHost(string $host): string
    {

        if (!$host) {
            throw new \InvalidArgumentException('Invalid Host');
        }

        if (!in_array($host, config('websocket.allowed_host'))) {
            throw new \InvalidArgumentException('Not allowed Host : ' . $host);
        }

        return $host;
    }

    protected function validatePort(string $port, ?string $schema): string
    {

        if (!$port && !$schema) {
            throw new \InvalidArgumentException('Invalid Port');
        }

        if (!$port && $schema) {
            $schema = $this->validateSchema($schema);

            if ($schema === Protocol::SCHEME_UNDERLYING) {
                $port = '80';
            } else if ($schema === Protocol::SCHEME_UNDERLYING_SECURE) {
                $port = '443';
            } else {
                throw new \InvalidArgumentException('Invalid Port');
            }
        }

        return $port;
    }

    protected function validateSchema(string $schema): string
    {
        if (!$schema) {
            throw new \InvalidArgumentException('Invalid Schema');
        }

        if (!in_array($schema, Protocol::SCHEMES)) {
            throw new \InvalidArgumentException('Unknown Socket Schema : ' . $schema);
        }

        if ($schema === Protocol::SCHEME_WEBSOCKET_SECURE) {
            return Protocol::SCHEME_UNDERLYING_SECURE;
        }

        return Protocol::SCHEME_UNDERLYING;
    }

}

?>