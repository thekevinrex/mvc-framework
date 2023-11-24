<?php

namespace PhpVueBridge\Bedrock;

use PhpVueBridge\Collection\Collection;

use app\core\bridge\BridgeServiceProvider;
use app\Core\DataBase\DataBaseServiceProvider;
use app\core\view\ViewServiceProvider;
use app\Core\Websockets\WebsocketsServiceProvider;

abstract class ServiceProvider
{


    protected $app;

    protected static array $services = [
        ViewServiceProvider::class,
        BridgeServiceProvider::class,
        DataBaseServiceProvider::class,
        WebsocketsServiceProvider::class,
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