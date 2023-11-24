<?php

namespace PhpVueBridge\Http\Request;

class ServerBag extends ParameterBag
{


    public function __construct(array $server)
    {

        if (
            !isset($server['PATH_INFO']) &&
            isset($_SERVER['PATH_INFO'])
        ) {
            $server['PATH_INFO'] = $_SERVER['PATH_INFO'];
        } else {
            $server['PATH_INFO'] = isset($_SERVER['REQUEST_URI'])
                ? parse_url($_SERVER['REQUEST_URI'])['path']
                : '/';
        }

        parent::__construct($server);
    }

    /**
     * 
     * Return the array of header from the server bag
     * @return array
     */
    public function getHeaders(): array
    {

        $headers = [];
        foreach ($this->parameters as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            } elseif (\in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    public function getProtocol(): string
    {
        return $this->get('SERVER_PROTOCOL');
    }
}
?>