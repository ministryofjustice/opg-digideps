<?php

declare(strict_types=1);

namespace App\EventDispatcher;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ObservableEventDispatcher
{
    /** @var array */
    private $dispatchedEvents = [];

    public function __construct(private readonly EventDispatcherInterface $dispatcher)
    {
    }

    public function dispatch(Event $event, string $eventName)
    {
        $this->dispatcher->dispatch($event, $eventName);
        $this->dispatchedEvents[] = $event;
    }

    public function getDispatchedEvents(): array
    {
        return $this->dispatchedEvents;
    }
}
