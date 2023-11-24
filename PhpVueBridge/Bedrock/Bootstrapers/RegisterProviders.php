<?php

namespace PhpVueBridge\Bedrock\Bootstrapers;

use PhpVueBridge\Bedrock\Application;
use PhpVueBridge\Bedrock\ProviderLoader;
use PhpVueBridge\Support\facades\Config;

class RegisterProviders
{

    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function bootstrap()
    {

        $providers = Config::get('app.providers', []);

        (new ProviderLoader($this->app))
            ->setCachePath($this->app->getBasePath() . '/bootstrap/Cache/providers.json')
            ->load($providers);
    }
}
?>