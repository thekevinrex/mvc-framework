<?php


namespace PhpVueBridge\Http\Request;

use PhpVueBridge\Http\Contracts\RequestContract;
use PhpVueBridge\Http\Request\ParameterBag;
use PhpVueBridge\Http\Request\ServerBag;

/**
 * The information about the http request
 */
class Request implements RequestContract
{

    const METHOD_OPTIONS = 'OPTIONS';

    const METHOD_GET = 'GET';

    const METHOD_POST = 'POST';

    const METHOD_PUT = 'PUT';

    const METHOD_DELETE = 'DELETE';

    const METHOD_PATCH = 'PATCH';

    /**
     * The path used to get the route
     */
    protected string $path;

    /**
     * The base path of the request
     */
    protected string $basePath;

    /**
     * The request uri path of the request
     */
    protected string $requestUri;

    /**
     * The request method (GET, POST, DELETE, PUT)
     */
    protected string $method;

    /**
     * The reponse format
     */
    protected ?string $format;

    protected string $sheme;

    protected string $host;

    /**
     * The request server bag ($_SERVER)
     */
    public ServerBag $server;

    /**
     * The request parameter bag ($_POST)
     */
    public ParameterBag $request;

    /**
     * The request cookies bag ($_COOKIE)
     */
    public ParameterBag $cookies;

    /**
     * The request querys ($_GET)
     */
    public ParameterBag $querys;

    /**
     * The request headers
     */
    public ParameterBag $headers;

    /**
     * The array of possibles reponse format
     */
    public array $formats = [
        'html' => ['text/html', 'application/xhtml+xml'],
        'txt' => ['text/plain'],
        'js' => ['application/javascript', 'application/x-javascript', 'text/javascript'],
        'css' => ['text/css'],
        'json' => ['application/json', 'application/x-json'],
        'jsonld' => ['application/ld+json'],
        'xml' => ['text/xml', 'application/xml', 'application/x-xml'],
        'rdf' => ['application/rdf+xml'],
        'atom' => ['application/atom+xml'],
        'rss' => ['application/rss+xml'],
        'form' => ['application/x-www-form-urlencoded', 'multipart/form-data'],
    ];

    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Initialize the request
     */
    public function initialize(): void
    {

        $server = array_replace([
            'REQUEST_METHOD' => 'GET',
            'REMOTE_ADDR' => '127.0.0.1',
            'QUERY_STRING' => '',
            'REQUEST_URI' => '/',
            'SCRIPT_NAME' => '',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '8000',
            'REQUEST_TIME' => time(),
        ], $_SERVER);

        $this->server = new ServerBag($server);
        $this->querys = new ParameterBag($_GET);
        $this->request = new ParameterBag($_POST);
        $this->cookies = new ParameterBag($_COOKIE);
        $this->headers = new ParameterBag($this->server->getHeaders());

        // echo '<pre>';
        // var_dump($server);
        // echo '</pre>';

        $this->setMethod();
    }

    /**
     * Get the base path of the request
     */
    public function getBasePath(): string
    {
        return $this->basePath ??= $this->setBasePath();
    }

    /**
     * Set the base path of the request
     */
    protected function setBasePath(): ?string
    {

        $basePath = null;
        $filename = basename($this->server->get('SCRIPT_FILENAME'));

        if (basename($this->server->get('SCRIPT_NAME')) == $filename)
            $basePath = $this->server->get('SCRIPT_NAME');

        $requestUri = $this->getRequestUri();

        if (!is_null($basePath) && !is_null($prefix = $this->getUrlPrefix($requestUri, $basePath))) {
            return $prefix;
        }

        if (!is_null($basePath) && !is_null($prefix = $this->getUrlPrefix($requestUri, rtrim(dirname($basePath), '/' . DIRECTORY_SEPARATOR)))) {
            return rtrim($prefix, '/' . DIRECTORY_SEPARATOR);
        }

        $basename = basename($basePath ?? '');

        if (empty($basename) || !strpos($requestUri, $basename)) {
            // no match whatsoever; set it blank
            return '';
        }

        if (is_null($basePath))
            return null;

        if (\strlen($requestUri) >= \strlen($basePath) && (false !== $pos = strpos($requestUri, $basePath)) && 0 !== $pos) {
            $basePath = substr($requestUri, 0, $pos + \strlen($basePath));
        }

        return rtrim($basePath, '/' . DIRECTORY_SEPARATOR);
    }

    /**
     * get the url prefix of the base path
     * @return string|null
     */
    protected function getUrlPrefix($string, $prefix): string|null
    {

        if (str_starts_with($string, $prefix)) {
            return $prefix;
        }

        return null;
    }

    /**
     * get the request uri of the request
     * @return string
     */
    public function getRequestUri(): string
    {
        return $this->requestUri ??= $this->setRequestUri();
    }

    /**
     * Set the request uri from the server
     * @return string
     */
    protected function setRequestUri(): string
    {
        return $this->server->get('REQUEST_URI', '/');
    }

    /**
     * Set the path used for the router
     * @param string $path
     * @return string
     */
    protected function setPath(): string
    {

        $path = $this->server->get('PATH_INFO', '/');

        if (is_null($baseUrl = $this->getBasePath())) {
            return $path;
        }

        $path = substr($path, \strlen($baseUrl));
        if (false === $path || '' === $path) {
            // If substr() returns false then PATH_INFO is set to an empty string
            $path = '/';
        }

        return $path;
    }

    /**
     * Set the request metho
     */
    protected function setMethod(): string
    {
        return $this->method = $this->server->get('REQUEST_METHOD');
    }

    /**
     * Get the request method
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get the path used for the router
     * @return string
     */
    public function getPath(): string
    {
        return $this->path ??= $this->setPath();
    }

    /**
     * Get the format of the reponse
     * @param mixed $format
     * @return string
     */
    public function getFormat(?string $mimeType = 'html'): ?string
    {
        return $this->format ??= $this->setFormat($mimeType);
    }

    /**
     * Set the format given a mimeType
     * @param string $mimeType
     * @return string|null
     */
    public function setFormat(?string $mimeType): ?string
    {

        $canonicalMimeType = null;
        if ($mimeType && false !== $pos = strpos($mimeType, ',')) {
            $canonicalMimeType = trim(substr($mimeType, 0, $pos));
        }

        if ($mimeType === null && $canonicalMimeType === null)
            return null;

        foreach ($this->formats as $format => $mimeTypes) {
            if (\in_array($mimeType, (array) $mimeTypes)) {
                return $format;
            }
            if (null !== $canonicalMimeType && \in_array($canonicalMimeType, (array) $mimeTypes)) {
                return $format;
            }
        }

        return null;
    }

    /**
     * Get the mime type of reponse from the given format
     * @param string $format
     * @return string
     */
    public function getMimeType(string $format): string
    {
        return
            isset($this->formats[$format])
            ? $this->formats[$format][0]
            : null;
    }

    public function getSheme()
    {
        return $this->sheme ??= $this->setSheme();
    }

    protected function setSheme()
    {

        if ($this->server->has('REQUEST_SCHEME')) {
            return $this->server->get('REQUEST_SCHEME');
        }

        $protocol = $this->server->getProtocol();

        if (str_starts_with($protocol, 'HTTPS')) {
            return 'https';
        }

        return 'http';
    }

    public function setHost()
    {
        return $this->getSheme() . '://' . $this->server->get('SERVER_NAME') . ':' . $this->server->get('SERVER_PORT') . '/';
    }

    public function getHost()
    {
        return $this->host ??= $this->setHost();
    }

    public function getFullPath()
    {
        return $this->getHost() . $this->getBasePath() . ($this->getPath() === '/' ? '' : $this->getPath());
    }

    public function getRoot()
    {
        return $this->getHost() . $this->getBasePath();
    }

    /**
     * Determine if the response should be a json response
     * @return bool
     */
    public function shouldReturnJson(): bool
    {
        return false;
    }
}
?>