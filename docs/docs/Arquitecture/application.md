---
sidebar_position: 2
---

# Application Core Class

## Introduction

The application class is the main core class of the framework. The application class is the glue that control and unite all the services providers.
What means that is the **glue** of the framework? It means that all the [lifecycle](./lifecycle) go through the application, is in charge of registering all the `services provider` and many other functions

**What uses you may have with the application class?**. It depends if you are building an application or a package and it can as useful as you gant to be, becouse:

- You can register a service provider
- Add callbacks to before and after bootstraping the application
- Add callbacks to before and after booting the application
- Add callbacks to when the lifecycle ends

in others words it gives you the opportunity to interact with all parts of the application.

## The bootstraping process

The bootstraping process starts when the `http kernel` or the `console kernel` receives a request in those files you can set the bootstrapers that the application will use, and you can add callbacks to before or after a bootstraper event.

:::tip
You can register those callbacks in any part of the application using the 'App' facade or the `app` helper function.
In any of the callbacks you can type-hint class the container will inject the class you need
:::

```php
<?php

    App::bootstrapping(YourBootstraper::class, function (Application $app) {
        // Your code
    });


    App::bootstrapped(YourBootstraper::class, function (Application $app) {
        // Your code
    });
?>
```

## The Boot process

The boot process start after the bootstraping process, while the bootstraping process focus in the bootstraper
the boot process focus in before or after the services providers boot.

```php
<?php

    App::booting(function (Application $app) {
        // Your code
    });


    App::booted(function (Application $app) {
        // Your code
    });
?>
```

## The Final Process

After that the kernel handle the request and the response is sended to the web browser. The terminate process is started.
And you can register callbacks.

```php
<?php
    App::terminate(function (Application $app) {
        // Your code
    });
?>
```
