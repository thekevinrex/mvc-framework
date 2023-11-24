<?php

namespace PhpVueBridge\Bedrock\Bootstrapers;

use PhpVueBridge\Bedrock\Application;
use PhpVueBridge\Bedrock\Contracts\HandlerContract;
use PhpVueBridge\Bedrock\Exceptions\Interfaces\FatalError;
use Throwable;

class HandleExceptions
{


    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function bootstrap()
    {

        error_reporting(-1);

        set_error_handler($this->handleError());

        set_exception_handler(fn(Throwable $e) => $this->HandleException($e));

        register_shutdown_function($this->handleShutdown());

        ini_set('display_errors', 'Off');
    }

    public function handleError()
    {
        return function ($level, $message, $file = '', $line = 0, $context = []) {
            if (in_array($level, [E_DEPRECATED, E_USER_DEPRECATED])) {
                throw new \ErrorException('E_DEPRECATED: ' . $message, 0, $level, $file, $line);
            } else if (error_reporting() & $level) {
                throw new \ErrorException($message, 0, $level, $file, $line);
            }
        };
    }

    public function handleException(Throwable $e)
    {


        //TODO Report error

        $this->app->resolve(
            HandlerContract::class
        )->response($e)->send();
    }

    public function handleShutdown()
    {
        return function () {
            if (
                !is_null($error = error_get_last()) &&
                in_array($error['type'], [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE])
            ) {
                $this->handleException(new FatalError($error['message'], 0, $error));
            }
        };
    }
}
?>