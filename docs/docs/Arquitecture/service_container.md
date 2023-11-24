---
sidebar_position: 3
---

# Service Container

The service container is a tool for managing class dependencies and dependencies injection.
Dependencies injection is a way to "inject" classes to the contructor or, in some cases "setters" and other methods

```php title="Example dependencies injection"
<?php

namespace app\App\Http\Controllers;

use app\Core\Request\Request;

class HomeController extends Controller {

    protected Request $request;

    public function __construct (Request $request) {
        $this->request = $request;
    }
}

?>
```

In this example we see how in the container contructor we inject the request without any other configuration

## Zero Configuration

If a class has no dependencies or only dependends on other concrete class (not interfaces), the container does not need to be instructed in how to resolve that class. It means that you can take advantage of the dependency injection without having to worry about writing many configuration files

Many of the classes that you will be writing in the application automatically recieves dependencies injection through the service container, including controllers, middlewares, etc.

## When to use dependencies injection

Thanks to the zero configuration, you can type-hint dependencies in controllers, middlewares and elsewhere manually without having to interact with the service container.

A important use of dependencies injection is the use of [facades](./facade). The facades are helpers class that you can inject that will help you to build applications without having to interact with the service container.

So **when to manually interact with the service container?**. You need to manually interact with the service container when you write a class that implements an interface and you wish to type-hint that interface.

## Binding

### Basic binding

The must commun scenarios in wich you will be binding class to the container is within the service providers.
In all the services provider you can have access to container through the `$this->app` property.

We can register a binding using the `Bind` method, passing the class or interface name that we want to bind along with a closure that returns a instance of the class.

```php
<?php

namespace app\App\Providers;

use app\Core\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {

    public function register () {

        $this->app->bind(YourClass::class, function ($app) {
            return new MyClass($app);
        });

    }
}
?>
```

Within the resolver you receive the container itself as an argument. We can use the container to resolve sub-dependencies of the class we are building

You can interact with the container outside of the services providers. You can doit by using the `App` [Facade](./facade)

```php
<?php

use app\Core\Support\Facades\App;

App::bind(YourClass::class, function ($app) {
    return new MyClass($app);
});
?>
```

:::tip
There is no need to bind class to the container if the class not implement any interface
:::

### Binding Shared

The `bindShared` method binds a class or interface to the container that should be resolved once. Once a bind shared is resolved, the same object is going to be shared and returned on nexts call into the container.

```php
$this->app->bindShared (YourClass::class, function ($app) {
    return new MyClass($app);
});
```

### Binding instances

You can also bind existing instances of classes to the container using the `instance` method. The given instance will be returned on nexts calls into the container

```php
$class = new MyClass();

$this->app->instance (YourClass::class, $class);
```

### Binding interfaces

A feature of the service container is that you can bind a interface to a given implementation.

```php
$this->app->bind (YourClassInterface::class, MyClass::class);
```

This statement tells to the container that when is type-hinted the interface that the container needs to resolve that class

## Contextual binding

Work in process
