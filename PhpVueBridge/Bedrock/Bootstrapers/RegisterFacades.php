<?php

namespace PhpVueBridge\Bedrock\Bootstrapers;

use PhpVueBridge\Bedrock\Application;
use PhpVueBridge\Support\Facades\Facade;


class RegisterFacades
{

    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function bootstrap()
    {
        Facade::clearResolvedIntancies();

        Facade::setApplication($this->app);
    }
}
?>