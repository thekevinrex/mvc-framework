<?php

namespace PhpVueBridge\Logger\Interfaces;

interface LoggerInterface
{
    /**
     * Log a message
     */
    public function log($level, string $message, array $context = []);

    /**
     * Log a interesting event
     */
    public function info(string $message, array $context = []);

    /**
     * Log a significant event
     */
    public function notice(string $message, array $context = []);

    /**
     * Alert that an action is required
     */
    public function alert(string $message, array $context = []);

    /**
     * Exceptional occurencies that are not errors
     */
    public function warning(string $message, array $context = []);

    /**
     * Errors that not require inmediate action but should be logged and monitored
     */
    public function error(string $message, array $context = []);

    /**
     * Log a critical condition
     */
    public function critical(string $message, array $context = []);

    /**
     * System has crashed (everybody run)
     */
    public function emergency(string $message, array $context = []);

    /**
     * Log a detailed debug information
     */
    public function debug(string $message, array $context = []);
}
?>