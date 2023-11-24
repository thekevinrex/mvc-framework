<?php


namespace PhpVueBridge\Routes;

use PhpVueBridge\Routes\Contracts\UrlGeneratorContract;
use PhpVueBridge\Http\Request\Request;

class UrlGenerator implements UrlGeneratorContract
{
    protected Router $router;

    protected Request $request;

    public function __construct(Router $router, Request $request)
    {
        $this->router = $router;
        $this->setRequest($request);
    }

    public function current(array $parameters = array())
    {
        return $this->to($this->request->getPath(), $parameters);
    }


    public function to($url, $parameters = array(), $secure = false)
    {

        if ($this->isValidUrl($url)) {
            return $url;
        }

        $root = $this->request->getRoot();

        [$url, $query] = $this->extractQuery($url);

        return $this->format(
            $root,
            $url
        ) . $query;
    }

    public function route($name, $parameters = array(), $secure = false)
    {
        if (!$url = $this->router->getRouteByName($name)) {
            throw new \InvalidArgumentException($url);
        }

        return $this->to($url, $parameters, $secure);
    }

    protected function format($root, $path)
    {

        $path = trim($path, '/');
        $root = trim($root, '/');

        return trim($root . '/' . $path, '/');
    }


    protected function extractQuery($url)
    {
        if (str_contains($url, '?')) {
            [$path, $query] = explode('?', $url, 2);

            return [$path, '?' . $query];
        }

        return [$url, ''];
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function isValidUrl($path)
    {
        if (!preg_match('~^(#|//|https?://|(mailto|tel|sms):)~', $path)) {
            return filter_var($path, FILTER_VALIDATE_URL) !== false;
        }

        return true;
    }

}

?>