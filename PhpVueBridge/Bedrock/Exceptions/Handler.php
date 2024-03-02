<?php

namespace PhpVueBridge\Bedrock\Exceptions;

use PhpVueBridge\Bedrock\Application;
use PhpVueBridge\Bedrock\Contracts\HandlerContract;
use PhpVueBridge\Http\Request\Request;
use PhpVueBridge\Http\Response\JsonResponse;
use PhpVueBridge\Http\Response\Response;
use PhpVueBridge\View\Errors\ErrorRender;


/**
 * Handle all the exception
 */
class Handler implements HandlerContract
{


    public function __construct(
        protected Application $app
    ) {

    }

    /**
     * Return the reponse to the exception
     * The reponse is prepared depending of the type of exception
     */
    public function response(\Throwable $e): Response
    {
        // If the exception has a render method we pass the render to the router to manage the response
        if (method_exists($e, 'render') && $response = $e->render()) {
            $this->app->resolve('router')->renderError($response);
            exit(1);
        }

        return $this->renderException($this->app->resolve('request'), $e);
    }

    /**
     * Render a default exception response
     */
    protected function renderException(Request $request, \Throwable $e): Response
    {
        return $request->shouldReturnJson()
            ? $this->toJsonResponse($e, $request)
            : $this->toResponse($this->prepareResponse($e), $request);
    }

    /**
     * Prepare the response of the exception
     */
    protected function toResponse(Response $response, Request $request): Response
    {
        return $response->prepare($request);
    }

    protected function toJsonResponse(\Throwable $e, Request $request): JsonResponse
    {
        var_dump($e);
        exit;
    }

    /**
     * create the response of the exception
     */
    protected function prepareResponse(\Throwable $e): Response
    {
        return new Response(
            $this->renderContent($e),
            500,
            []
        );
    }

    /**
     * render the content of the exception to a friendly user experience
     * @param \Throwable $e
     * @return string
     */
    protected function renderContent(\Throwable $e)
    {
        if (!$this->app->isBootstraped()) {
            return (new ErrorRender($e))->render(false);
        }

        if (config('app.debug')) {
            return (new ErrorRender($e))->debug();
        } else {
            return (new ErrorRender($e))->render();
        }
    }
}
?>