---
title: "Services Provider"
sidebar_position: 4
---

# Services Provider

The services provider are the central pieces for configuring the application. You own application as well as the framework core services are configured in the services providers.

In the services providers you are going to register everyting, that includes registering service container bindings, event listeners, middlewares and even routes.

In the `Config/app.php` file you will find an array of services providers. These are all the providers that the application will be loaded for the application.
By default, there is set a list of the core services of the framework. This providers register core components of the framework like the database, websocket, bridge and other services.
Some of this services are "deferred" providers, it means that will be not loaded in every lifecycle, but instead will be loaded when the services that provide are actually needed.

## Writing services providers

All services providers extends the `PhpVueBridge\Bedrock\ServiceProvider` class. The service provider must container a `register` or `boot` method

## The register method

In the register method you should only bind things to the container. Never attempt to register any event listener, routes nor any pieces of functionality within the `register` method. Becouse you may accidentally use a service that has not been loaded yet

Within any of your services providers you have access to the container through the `$this->app` property.

```php
<?php

    public function register() : void {
        $this->app->bind(YourClass::class, function ($app) {
            return new MyClass($app);
        });
    }
?>
```

### The `bindings` and `bindingsShareds` properties

If your service provider only registers simple bindings, you may wish to use the `bindings` and `bindingsShareds` properties instead of manually registering them. When the service provider is loaded by the framework, it will be automatically check for those properties and register there bindings

```php
<?php
class MyServiceProvider extends ServiceProvider {

    public array $bindings = [
        YourClass::class => MyClass::class,
    ];

    public array $bindingsShareds = [
        YourSharedClass::class => MySharedClass::class,
    ];
}
?>
```

## The boot method

This method is called when the application registers all the service providers, meaning that you have access to all the services registered by the framework, so you can add functionality to the application.

```php
<?php
    use PhpVueBridge\Support\Facades\Route;

    public function boot() : void {

        Route::get('/', function() {
            return view ('index');
        });

    }
?>
```

### Boot method dependencies injection

In your boot method you can type-hint dependencies. The service container will automatically inject those dependencies to the boot method:

```php
<?php
    use PhpVueBridge\Bridge\Bridge;

    public function boot(Bridge $bridge) : void {

        $bridge->head()->title('Booting services');

    }
?>
```

## Registering services providers

All services providers are registered in the `config/app.php` file. This file contains an array of service providers where you can list the class names of your services.

By default a set of the framework services are registered in this array.

To register yours services providers added to the array

```php
'providers' => [
    ViewServiceProvider::class,
    BridgeServiceProvider::class,
    DataBaseServiceProvider::class,
    WebsocketsServiceProvider::class,


    RouteServiceProvider::class,
    WebsocketServiceProvider::class,
]
```

## Deferred Service Provider

work in progress
