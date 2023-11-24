<?php

/**
 * 
 * This is the entry point of all request to the framework
 * This is the base file that loads all the core framework files
 * 
 * In conclusion this is the most important files
 */

$applicationStart = microtime(true);

/**
 * This load the composer autoload
 */
require __DIR__ . '/../vendor/autoload.php';

/**
 * It get a fresh instance of the base core application
 */
$app = require_once __DIR__ . '/../bootstrap/app_start.php';

/**
 * 
 * This resolve the http contract of the container and handle the incoming request
 * Giving a proper response to the user
 */
$response = $app->resolve(\PhpVueBridge\Http\Contracts\HttpContract::class)->handleIncomingRequest(
    new \PhpVueBridge\Http\Request\Request()
);

$response->send();

// trigger_error("SDas");
// $s = log(-4, -3);
// spl_autoload_register('sdada');

$app->close();

?>