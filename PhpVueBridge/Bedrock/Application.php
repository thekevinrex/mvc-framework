<?php

namespace PhpVueBridge\Bedrock;

use PhpVueBridge\Bedrock\Container\Container;
use PhpVueBridge\Bedrock\Contracts\ApplicationContract;
use PhpVueBridge\Events\EventServiceProvider;
use PhpVueBridge\Logger\LoggerServiceProvider;
use PhpVueBridge\Routes\RouteServiceProvider;

class Application extends Container implements ApplicationContract
{

    /**
     * 
     * The actual version of the application
     */
    protected const VERSION = 0.1;

    /**
     * The enviroment that the application is running
     */
    protected static string $enviroment = 'production';

    /**
     * The base path for the application
     */
    protected string $basePath;

    /**
     * An static access to the application that works like a facades
     */
    protected static Application $instance;

    /**
     * 
     * If the application has been bootstraped before
     */
    protected bool $hasBeenBostraped = false;

    /**
     * 
     * If the application has booted
     */
    protected $booted = false;

    /**
     * 
     * Register all the services providers
     */
    protected array $servicesProviders = [];

    /**
     * Register if a service providers has been loaded
     */
    protected array $loadedServicesProviders = [];

    /**
     * The deferred providers (it load when the app is booting)
     */
    protected array $deferredServiceProvider = [];

    /**
     * The booting callbacks
     */
    protected array $bootingCallback = [];

    /**
     * The booted callbacks
     */
    protected array $bootedCallback = [];

    /**
     * The bootstrapping callback (When the application is loading the bootstrapper)
     */
    protected array $bootstrappingCallback = [];

    /**
     * The bootstrapped callback (When the application finish of loading the bootstrapper)
     */
    protected array $bootstrappedCallback = [];

    /**
     * The terminated callback (When the application is terminated)
     */
    protected array $terminatedCallback = [];

    /**
     * 
     * App folder path outside of the core
     */
    protected static string $appPath = '';

    /**
     * 
     * The resource folder path
     */
    protected static string $resourcesPath = '';

    /**
     * The Storage folder path
     */
    protected static string $storagePath = '';

    /**
     * The Database forlder path
     */
    protected static string $dataBasePath = '';

    /**
     * 
     * The config folder path, this is where the config files are saved
     */
    protected static string $configPath = '';

    /**
     * The routes folder path
     */
    protected static string $routesPath = '';


    /**
     * @param string $basePath
     */
    public function __construct(string $basePath)
    {

        $this->setBasePath($basePath);

        self::$instance = $this;

        $this->bindCoreClass();
        $this->registerCoreBaseProviders();
        $this->registerCoreAlias();
    }

    /**
     * Bind the core class to the container
     */
    private function bindCoreClass(): void
    {
        $this->instance('app', $this);
        $this->instance(Container::class, $this);
    }

    /**
     * Register the core providers
     */
    private function registerCoreBaseProviders(): void
    {
        $this->registerServiceProvider(LoggerServiceProvider::class);
        $this->registerServiceProvider(EventServiceProvider::class);
        $this->registerServiceProvider(RouteServiceProvider::class);
    }

    /**
     * Resolve a class fron the container
     */
    public function resolve($class, array $param = [], bool $events = false): mixed
    {
        $class = $this->getAlias($class);

        if (isset($this->deferredServiceProvider[$class]) && !$this->instances[$class]) {
            $this->loadDeferredProvider($class);
        }

        return parent::resolve($class, $param, $events);
    }

    /**
     * Verify is the app has been booted
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     * Verify is the app has been bootstrapped
     */
    public function isBootstraped(): bool
    {
        return $this->hasBeenBostraped;
    }

    /**
     * Register a service provider and if the app is booted it boot the service provider
     * @param string|ServiceProvider $provider
     */
    public function registerServiceProvider($provider, bool $force = false): ServiceProvider|bool
    {

        if ($registeredProvider = $this->getProvider($provider) && !$force) {
            return $registeredProvider;
        }

        if (is_string($provider)) {
            $provider = $this->resolveProvider($provider);
        }

        $provider->register();

        if (property_exists($provider, 'bindings')) {
            foreach ($provider->bindings as $key => $value) {
                $this->bind($key, $value);
            }
        }

        if (property_exists($provider, 'bindingsShareds')) {
            foreach ($provider->bindingsShareds as $key => $value) {
                $key = is_int($key) ? $value : $key;

                $this->bindShared($key, $value);
            }
        }

        $this->servicesProviders[] = $provider;
        $this->loadedServicesProviders[get_class($provider)] = true;

        if ($this->isBooted())
            $this->bootProvider($provider);

        return $provider;
    }

    /**
     * Register a deferred service provider
     */
    public function registerDeferredServiceProvider($provider, $service = null): void
    {

        if ($service) {
            unset($this->deferredServiceProvider[$service]);
        }

        $this->registerServiceProvider($provider = $this->resolveProvider($provider));

        if (!$this->isBooted()) {
            $this->booting(function () use ($provider) {
                $this->bootProvider($provider);
            });
        }
    }

    /**
     * Add a deferred service provider for resolve when is nedded
     */
    public function addDeferredServices(array $deferredServices): void
    {
        $this->deferredServiceProvider = array_merge($this->deferredServiceProvider, $deferredServices);
    }

    /**
     * Loads all the deferred providers
     */
    public function loadDeferredProviders(): void
    {
        array_walk($this->deferredServiceProvider, function ($provider, $service) {
            $this->loadDeferredProvider($service);
        });

        $this->deferredServiceProvider = [];
    }

    /**
     * Load a deferred service provider for a given service
     */
    public function loadDeferredProvider($service = null): void
    {
        if (!isset($this->deferredServiceProvider[$service])) {
            return;
        }

        $provider = $this->deferredServiceProvider[$service];

        if (!isset($this->loadedServicesProviders[$provider])) {
            $this->registerDeferredServiceProvider($provider, $service);
        }
    }

    /**
     * Summary of getProvider
     * @param string|ServiceProvider $provider
     * @return ServiceProvider|null
     */
    private function getProvider($provider)
    {
        return $this->getProviders($provider)[0] ?? null;
    }

    /**
     * Returns an array of the services provider that are instance of the given provider
     * @param ServiceProvider|string $provider
     */
    private function getProviders($provider): array
    {

        $provider = is_string($provider) ? $provider : get_class($provider);

        return array_filter($this->servicesProviders, function ($item) use ($provider) {
            return $item instanceof $provider;
        });
    }

    /**
     * In case that a given provider is a string it return an instance of the provider
     */
    public function resolveProvider(string $provider): ServiceProvider
    {
        return new $provider($this);
    }

    /**
     * It boot the giver service provider
     */
    public function bootProvider(ServiceProvider $provider): void
    {

        if (method_exists($provider, 'boot')) {
            $this->call([$provider, 'boot']);
        }

        // #todo work in progress

    }

    /**
     * Set the BasePath of the app
     */
    public function setBasePath(string $basePath): self
    {

        $this->basePath = rtrim($basePath);

        return $this;
    }

    /**
     * get the BasePath of the app
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Set the AppPath of the app
     */
    public function setAppPath(string $appPath = null): self
    {

        self::$appPath = $this->getBasePath() . '/' . (is_null($appPath) ? 'app' : $appPath);

        return $this;
    }

    /**
     * Get the AppPath of the app
     */
    public function getAppPath(): string
    {
        return self::$appPath;
    }

    /**
     * Set the ResourcePath of the app
     */
    public function setResourcesPath(string $resourcesPath = null): self
    {

        self::$resourcesPath = $this->getBasePath() . '/' . (is_null($resourcesPath) ? 'resources' : $resourcesPath);

        return $this;
    }

    /**
     * Get the ResourcePath of the app
     */
    public function getResourcesPath(): string
    {
        return self::$resourcesPath;
    }

    /**
     * Set the StoragePath of the app
     */
    public function setStoragePath(string $storagePath = null): self
    {

        self::$storagePath = $this->getBasePath() . '/' . (is_null($storagePath) ? 'storage' : $storagePath);

        return $this;
    }

    /**
     * Get the StoragePath of the app
     */
    public function getStoragePath(): string
    {
        return self::$storagePath;
    }

    /**
     * Set the RoutesPath of the app
     */
    public function setRoutesPath(string $routesPath = null): self
    {

        self::$routesPath = $this->getBasePath() . '/' . (is_null($routesPath) ? 'routes' : $routesPath);

        return $this;
    }

    /**
     * Get the RoutesPath of the app
     */
    public function getRoutesPath(): string
    {
        return self::$routesPath;
    }

    /**
     * Set the ConfigPath of the app
     */
    public function setConfigPath(string $configPath = null): self
    {

        self::$configPath = $this->getBasePath() . '/' . (is_null($configPath) ? 'config' : $configPath);

        return $this;
    }

    /**
     * Get the ConfigPath of the app
     */
    public function getConfigPath(): string
    {
        return self::$configPath;
    }

    /**
     * Set the DataBasePath of the app
     */
    public function setDataBasePath(string $dataBasePath = null): self
    {

        self::$dataBasePath = $this->getBasePath() . '/' . (is_null($dataBasePath) ? 'database' : $dataBasePath);

        return $this;
    }

    /**
     * Get the DataBasePath of the app
     */
    public function getDataBasePath(): string
    {
        return self::$dataBasePath;
    }

    /**
     * Get the enviroment that the app is running
     */
    public static function enviroment(): string
    {
        return self::$enviroment;
    }

    /**
     * Get the enviroment file to load
     * If the app enviroment is 'Development' it return an empty string
     */
    public function getEnviromentFile(): string
    {

        $enviroment = match (self::$enviroment) {
            'development' => 'dev',
            'production' => 'prod',
            'console' => '',
            default => self::$enviroment
        };

        $file = '.env.' . $enviroment;

        if (!file_exists($this->getEnviromentPath() . '/' . $file)) {
            $file = '.env';
        }

        return $file;
    }

    /**
     * get the enviroment base path
     */
    public function getEnviromentPath(): string
    {
        return $this->getBasePath();
    }

    /**
     * Set the app enviroment
     */
    public function setActualEnviroment(string $enviroment): void
    {
        self::$enviroment = $enviroment;
    }

    public function fireCallbacks(array $callbacks): void
    {
        foreach ($callbacks as $key => $callback) {
            $this->call($callback, null, [$this]);

            unset($callbacks[$key]);
        }
    }

    /**
     * It bootstrap the application with the given bootstrapers and boot the app
     */
    public function bootstrap(array $bootstrapper): void
    {
        foreach ($bootstrapper as $bootstrap) {
            $this->fireCallbacks($this->bootstrappingCallback[$bootstrap] ?? []);

            $this->resolve($bootstrap)->bootstrap();

            $this->fireCallbacks($this->bootstrappedCallback[$bootstrap] ?? []);
        }

        $this->hasBeenBostraped = true;

        $this->boot();
    }

    /**
     * Register a callback before bootstrapping
     */
    public function bootstrapping($bootstrap, $callback)
    {
        $this->bootstrappingCallback[$bootstrap] = $callback;
    }

    /**
     * Register a callback after bootstrapping
     */
    public function bootstrapped($bootstrap, $callback)
    {
        $this->bootstrappedCallback[$bootstrap] = $callback;
    }

    /**
     * Register important aliases
     */
    private function registerCoreAlias()
    {

        $defaultAlias = [
            'app' => [
                self::class,
                ApplicationContract::class,
                Container::class,
                \PhpVueBridge\Bedrock\Contracts\ContainerContract::class
            ],
            'logger' => [
                \PhpVueBridge\Logger\Interfaces\LoggerInterface::class,
                \PhpVueBridge\Logger\StdOutLogger::class
            ],
            'http' => [
                \PhpVueBridge\Http\Http::class,
                \PhpVueBridge\Http\Contracts\HttpContract::class
            ],
            'router' => [
                \PhpVueBridge\Routes\Router::class,
                \PhpVueBridge\Routes\Contracts\RouterContract::class
            ],
            'request' => [
                \PhpVueBridge\Http\Request\Request::class,
                \PhpVueBridge\Http\Contracts\RequestContract::class
            ],
            'handler' => [
                \PhpVueBridge\Bedrock\Exceptions\Handler::class,
                \PhpVueBridge\Bedrock\Contracts\HandlerContract::class
            ],
            'config' => [
                \PhpVueBridge\Bedrock\Contracts\ConfigContract::class,
                \PhpVueBridge\Bedrock\Config::class
            ],
            'view' => [
                \PhpVueBridge\View\ViewFactory::class,
                \PhpVueBridge\View\View::class,
                \PhpVueBridge\Contracts\ViewContract::class
            ],
            'compiler' => [
                \PhpVueBridge\View\Compilers\Compiler::class,
                \PhpVueBridge\View\Compilers\ViewCompiler::class
            ],
            'engine' => [
                \PhpVueBridge\View\Engines\CompilerEngine::class,
                \PhpVueBridge\View\Engines\Engine::class,
                \PhpVueBridge\View\Engines\PhPEngine::class
            ],
            'db' => [
                \PhpVueBridge\DataBase\DataBaseManager::class
            ],
            'bridge' => [
                \PhpVueBridge\bridge\Bridge::class,
                \PhpVueBridge\contracts\BridgeContract::class
            ]
        ];

        foreach ($defaultAlias as $key => $aliases) {
            foreach ($aliases as $index => $alias) {
                $this->setAlias($key, $alias);
            }
        }
    }

    /**
     * Boot the application and make that each provider boot
     * @return void
     */
    public function boot()
    {

        if ($this->isBooted())
            return;

        $this->fireCallbacks($this->bootingCallback);

        array_walk($this->servicesProviders, function ($p) {
            $this->bootProvider($p);
        });

        $this->fireCallbacks($this->bootedCallback);

        $this->booted = true;
    }

    public function booting($callback)
    {
        $this->bootingCallback[] = $callback;
    }

    public function booted($callback)
    {
        $this->bootedCallback[] = $callback;
    }

    public function terminate($callback)
    {
        $this->terminatedCallback[] = $callback;
    }

    /**
     * Run the application
     * @return void
     */
    public function close()
    {

        if (!$this->isBooted())
            $this->boot();

        $this->fireCallbacks($this->terminatedCallback);
    }

}

?>