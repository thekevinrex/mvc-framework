<?php


namespace PhpVueBridge\Routes\Cors;

use PhpVueBridge\Http\Request\Request;
use PhpVueBridge\Http\Response\Response;
use PhpVueBridge\Routes\Contracts\CorsContract;

class CorsHandler implements CorsContract {

    protected array $allowedHosts = [];

    protected Request $request;

    public function __construct(array $options = []) {
        foreach($options as $key => $value) {
            if(isset($this->{$key})) {
                $this->{$key} = $value;
            }
        }
    }

    public function isPreflightRequest(Request $request) {
        return $request->getMethod() === Request::METHOD_OPTIONS && $request->headers->has('Access-Control-Request-Method');
    }

    public function addActualRequestHeaders(Response $response, Request $request): Response {
        $this->request = $request;

        $this->configureAllowedOrigins($response);

        // if($response->headers->has('Access-Control-Allow-Origin')) {
        //     $this->configureAllowCredentials($response, $request);

        //     $this->configureExposedHeaders($response, $request);
        // }

        return $response;
    }

    public function handlePreflightRequest(Request $request): Response {
        $this->request = $request;

        $response = new Response('', 204);

        $this->configureAllowedOrigins($response);

        $this->configureAllowedMethods($response);

        $this->varyHeader($response, 'Access-Control-Request-Method');

        return $response;
    }

    public function configure(array $options = []) {
        # code...
    }

    protected function configureAllowedOrigins(Response $response): void {
        $response->headers->set('Access-Control-Allow-Origin', '*');
    }

    protected function configureAllowedMethods(Response $response): void {
        $allowMethods = strtoupper((string)$this->request->headers->get('Access-Control-Request-Method'));

        $this->varyHeader($response, 'Access-Control-Request-Method');

        $response->headers->set('Access-Control-Allow-Methods', $allowMethods);
    }

    public function varyHeader(Response $response, string $header): Response {
        if(!$response->headers->has('Vary')) {
            $response->headers->set('Vary', $header);
        } elseif(!in_array($header, explode(', ', (string)$response->headers->get('Vary')))) {
            $response->headers->set('Vary', ((string)$response->headers->get('Vary')).', '.$header);
        }

        return $response;
    }
}

?>