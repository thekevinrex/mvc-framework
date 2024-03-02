<?php

namespace app\Core\Websockets\Utils;

use app\Core\Websockets\Protocols\HeaderBag;

class WSRequest
{

    const REQUEST_LINE_REGEX = '/^GET (\S+) HTTP\/1.1$/';

    protected string $original;

    protected string $request;

    protected string $body;

    protected string $headers;

    public function __construct(string $request)
    {
        $this->original = $request;

        $eol = stripos($request, "\r\n");

        if ($eol === false) {
            throw new \InvalidArgumentException('Invalid request line');
        }

        [
            $this->request,
            $this->headers,
            $this->body
        ] = $this->splitRequestIntoComponents($request, $eol);
    }

    protected function splitRequestIntoComponents($response, $eol): array
    {
        $request = substr($response, 0, $eol);
        $response = substr($response, $eol + 2);

        $parts = explode("\r\n\r\n", $response, 2);

        if (count($parts) < 2) {
            $parts[] = '';
        }

        [$headers, $body] = $parts;

        return [
            $request,
            $headers,
            $body,
        ];
    }

    public function getHeaders(): HeaderBag
    {
        return new HeaderBag($this->headers);
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getRequest(): string
    {
        return $this->request;
    }

    public function validateRequestLine(): array
    {
        $matches = [0 => null, 1 => null];

        if (!preg_match(self::REQUEST_LINE_REGEX, $this->request, $matches) || !$matches[1]) {
            throw new \Exception('Invalid request line', 400);
        }

        $url = parse_url($matches[1]);

        return [$url['path'] ?? null, $url['query'] ?? null];
    }
}
?>