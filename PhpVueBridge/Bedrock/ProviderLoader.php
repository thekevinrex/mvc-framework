<?php


namespace PhpVueBridge\Bedrock;

use PhpVueBridge\Bedrock\Interfaces\DeferredProvider;
use PhpVueBridge\Collection\Interfaces\CollectionInterface;

class ProviderLoader
{

    protected Application $app;

    protected string $cachePath;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function setCachePath(string $path)
    {
        $this->cachePath = $path;

        return $this;
    }

    public function load(array|CollectionInterface $providers)
    {
        if ($providers instanceof CollectionInterface) {
            $providers = $providers->toArray();
        }

        $cacheProviders = $this->loadCacheProviders();

        if (is_null($cacheProviders) || $cacheProviders['providers'] != $providers) {
            $cacheProviders = $this->cacheProviders($providers);
        }

        $this->app->addDeferredServices($cacheProviders['deferred']);

        foreach ($cacheProviders['load'] as $provider) {
            $this->app->registerServiceProvider($provider, true);
        }
    }

    protected function loadCacheProviders()
    {
        if (!file_exists($this->cachePath))
            return null;

        return json_decode(file_get_contents($this->cachePath), true);
    }

    protected function cacheProviders(array $providers)
    {

        $cached = ['providers' => $providers, 'load' => [], 'deferred' => []];

        foreach ($providers as $provider) {

            $instance = $this->app->resolveProvider($provider);

            if ($instance instanceof DeferredProvider) {
                foreach ($instance->provides() as $service) {
                    $cached['deferred'][$service] = $provider;
                }
            } else {
                $cached['load'][] = $provider;
            }
        }

        return $this->writeToCache($cached);
    }

    protected function writeToCache(array $cached)
    {
        file_put_contents($this->cachePath, json_encode($cached, JSON_PRETTY_PRINT));

        return $cached;
    }
}