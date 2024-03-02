<?php

namespace PhpVueBridge\Bedrock;

use PhpVueBridge\Collection\Collection;
use PhpVueBridge\View\ViewServiceProvider;
use PhpVueBridge\Bridge\BridgeServiceProvider;

abstract class ServiceProvider
{

    protected Application $app;

    protected static array $services = [
        ViewServiceProvider::class,
        BridgeServiceProvider::class,
        // DataBaseServiceProvider::class,
        // WebsocketsServiceProvider::class,
    ];

    public array $bindings = [];

    public array $bindingsShareds = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public abstract function register(): void;

    public static function getDefaultProviders(): Collection
    {
        return new Collection(static::$services);
    }

}

?>