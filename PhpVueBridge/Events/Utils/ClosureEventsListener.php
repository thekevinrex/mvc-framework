<?php

namespace PhpVueBridge\Events\Utils;

use PhpVueBridge\Events\EventManager;
use PhpVueBridge\Events\Exceptions\InvalidParameterException;
use PhpVueBridge\Support\Util;

class ClosureEventsListener
{

    protected \Closure $events;

    protected EventManager $manager;

    protected array $parameters = [];

    public function __construct(EventManager $manager, \Closure $events)
    {
        $this->events = $events;
        $this->manager = $manager;

        $this->getParametersTypes();
    }

    public function registerListeners(): void
    {
        $this->manager->listen($this->getFirstParameterType(), $this->events);
    }

    protected function getFirstParameterType(): string
    {
        $parameters = array_values($this->parameters);

        if (!$parameters || empty($parameters)) {
            throw new InvalidParameterException('The given Closure has no parameters.');
        }

        if (reset($parameters) == null) {
            throw new InvalidParameterException('The first parameter of the given Closure is missing a type hint.');
        }

        return reset($parameters);
    }

    protected function getParametersTypes(): array
    {
        $parameters = (new \ReflectionFunction($this->events))->getParameters();

        foreach ($parameters as $parameter) {
            if ($parameter->isVariadic()) {
                $this->parameters[$parameter->getName()] = null;
            }

            $this->parameters[$parameter->getName()] = Util::getParameterClassName($parameter);
        }

        return $this->parameters;
    }
}
?>