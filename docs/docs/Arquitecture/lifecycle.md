---
title: "Lifecycle of the Application"
description: "Tour on the application request lifecycle"
sidebar_position: 1
---

# Lifecycle Of the application

## First step

Every request to the application be an ajax request or a normal request is directed to the `public/index.php` file.
In this file there is no much code, in fact the only propose of this file is be the start point to load the whole framework

The `index.php` loads the composer generated autoload file, and then create a new instance of the application located in `bootstrap/app.php`.
The first step of the application is to create a new instance of the [Application](./application) / [Service Container](./service_container)

## HTTP / Console

Next the incoming request is directly send to the HTTP kernel or the Console Kernel depending the type of request.
This files serve as the central location that all requests flow.
First let see the HTTP Kernel that is located in `app/Http/Http.php`.

The Http kernel extends the core class `app\Core\Http\Http.php` class. This class defines an array of bootstrapper for the application.
Each bootstrapper is in charge of configuring a part of the application, like detect the [application enviroment](./enviroment), configure [error handling](./error_handling), configure [logging](./loggin) and other task. One of the must important task is to register the [service providers](./services_providers). That we will see later.

The Http Kernel also defines a list of [middlewares](./middlewares) that all request must pass through before being handled by the application.

The main method of the Http Kernel or the Console Kernel is the `handle` method. This method function is quite simple, it recieves a `Request` and return a `Response`.

## Services Providers

One of the must important bootstraper action is to load the services provider. The services provider are responsable of loading all the framework components like routing, database, views, etc. All the services provider are located in the array providers app cofiguration file located in `config/app.php`.

The framework it will loaded all the services provider and then instantiate each. After instantiating the services providers it will call the register method on each providers. Then in the boot process of the application the boot method will be called on each provider.

In resume each of the framework components is registered and configured inside one of the services providers. Since the services providers register and configure each component thy are the must important aspeck of the framework

## Routing

One of the must important service provider of the application is the routing service provider located in `app/providers/RouteServiceProvider.php`. This service loads all the routes files located in the `Routes` directory

Once the application has bootstrapped and all the services have been registered then the application is ready to send the request to the router for dispatching. In the router the request will be dispatched to a specific route controller class and run any specific route middleware.

Middleware classes are a convinient method to filter or examining the Http request entering the application. Some middlewares are assigned to all the routes, like those defined in the `$middlewares` property in the `app/Http/Http.php` class, while some are assigned to an specific route or route groups.

If the request pass all the middlewares. The route or controller method will be excecuted and the response returned by the route or controller method will be sent back to the router.

## Last Step

Once the route or controller method returns a `Response`, the response will travel back through the routes middlewares giving the application a last chance to modify or examine the response.

Finally, the `handle` method return the `Response` object and the `index.php` file call the send method on the returned `Response` object. The `send` method sends the reponse content to the user web browser. Last the application terminate all the and close all the services.

**We have finished** our tour for the application request lifecycle

# Resuming:

- New request to the index.php
- Created new instance of the application
- Kernel handle the request
- The application is bootstrapped
- The service provider are registered
- Request send to the router
- Request pass all the route middleware
- An response is sent back to the router
- The index.php send the response back to the user browser
- The Application terminate
