<?php
namespace PhpVueBridge\Logger;

use PhpVueBridge\Logger\Interfaces\LoggerInterface;
use PhpVueBridge\Logger\Utils\LoggerLevel;

abstract class Logger implements LoggerInterface
{
    public function alert(string $message, array $context = [])
    {
        return $this->log(LoggerLevel::ALERT, $message, $context);
    }

    public function info(string $message, array $context = [])
    {
        return $this->log(LoggerLevel::INFO, $message, $context);
    }

    public function notice(string $message, array $context = [])
    {
        return $this->log(LoggerLevel::NOTICE, $message, $context);
    }

    public function error(string $message, array $context = [])
    {
        return $this->log(LoggerLevel::ERROR, $message, $context);
    }

    public function warning(string $message, array $context = [])
    {
        return $this->log(LoggerLevel::WARNING, $message, $context);
    }

    public function critical(string $message, array $context = [])
    {
        return $this->log(LoggerLevel::CRITICAL, $message, $context);
    }

    public function emergency(string $message, array $context = [])
    {
        return $this->log(LoggerLevel::EMERGENCY, $message, $context);
    }

    public function debug(string $message, array $context = [])
    {
        return $this->log(LoggerLevel::DEBUG, $message, $context);
    }
}
?>