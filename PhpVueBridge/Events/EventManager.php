<?php


namespace PhpVueBridge\Events;

use PhpVueBridge\Bedrock\Application;
use PhpVueBridge\BroadCasting\ShouldBroadCast;
use PhpVueBridge\Events\Contracts\EventContract;
use PhpVueBridge\Events\Utils\ClosureEventsListener;

class EventManager implements EventContract
{

    protected Application $app;

    protected array $listeners = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function listen($events, $listener = null): void
    {

        if ($events instanceof \Closure) {
            return (new ClosureEventsListener($this, $events))->registerListeners();
        }

        if (is_array($events)) {
            foreach ($events as $event) {
                $this->listen($event, $listener);
            }
        } else {
            $this->listeners[$events][] = $listener;
        }
    }

    public function hasListener($event): bool
    {
        return isset($this->listeners[$event]);
    }

    public function dispatch($event, $data = [], $limit = false): mixed
    {

        [$event, $data] = $this->parseEventData($event, $data);

        if ($this->shouldBroadCast($data)) {
            $this->broadCastEvent($data);
        }

        $responses = [];

        foreach ($this->getListeners($event) as $listener) {

            $response = $this->dispatchEvent(
                $listener,
                $event,
                $data
            );

            if ($limit && !is_null($response)) {
                return $response;
            }

            if ($response == false) {
                break;
            }

            $responses[] = $response;
        }

        return $responses;
    }

    protected function dispatchEvent(
        $listener,
        $event,
        $data = []
    ) {
        return (
            new EventDispatcher($this->app, $listener)
        )->dispatch(
                $event,
                $data
            );
    }


    protected function parseEventData($event, array $data): array
    {
        if (is_object($event)) {
            return [get_class($event), [$event]];
        }

        return [
            $event,
            is_array($data) ? $data : [$data]
        ];
    }

    protected function shouldBroadCast($event): bool
    {
        return isset($event) && $event instanceof ShouldBroadCast && $this->broadCastWhen($event);
    }

    protected function broadCastWhen($event): bool
    {
        return (method_exists($event, 'broadCastWhen'))
            ? $event->broadCastWhen()
            : true;
    }

    protected function broadCastEvent($event)
    {
        # code...
    }

    protected function getListeners(string $event): array
    {
        return array_merge(
            $this->listeners[$event] ?? [],
            (class_exists($event, false)
                ? $this->getInterfacesListeners($event)
                : []
            )
        );
    }

    public function getInterfacesListeners(string $event): array
    {
        $listeners = [];

        foreach (class_implements($event) as $interface) {
            if (isset($this->listeners[$interface])) {
                $listeners = array_merge(
                    $listeners,
                    $this->listeners[$interface],
                );
            }
        }

        return $listeners;
    }
}
?>