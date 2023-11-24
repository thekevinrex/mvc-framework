<?php

/**
 * Create a new core application
 * 
 * This serve as the glue to join all the services and components of the application
 * This is the responsable of binding all class, contracts, intefaces, facades etc to the container of the application
 * The container is the responsable of making the dependency injection into the class constructor
 * 
 */
$app = new \PhpVueBridge\Bedrock\Application(
    dirname(__DIR__)
);

/**
 * 
 * This set all the base paths of the application
 */
$app->setAppPath()
    ->setConfigPath()
    ->setResourcesPath()
    ->setRoutesPath()
    ->setStoragePath()
    ->setDataBasePath();


/**
 * 
 * Bind to the container important contracts
 * The http contract is the responsable of handling all the incoming request and returning the corresponding response
 * And the Handler Contract is the responsable that in case an error occurs when the app is running or building those error dont crash the app and wive the users a frendly response page
 */
$app->bindShared(
    \PhpVueBridge\Http\Contracts\HttpContract::class,
    \App\Http\Http::class
);

$app->bindShared(
    \PhpVueBridge\Console\Contracts\ConsoleContract::class,
    \App\Console\Handler::class
);

$app->bindShared(
    \PhpVueBridge\Bedrock\Contracts\HandlerContract::class,
    \App\Exceptions\Handler::class
);

return $app;