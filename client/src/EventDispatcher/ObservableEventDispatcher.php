<?php declare(strict_types=1);


namespace App\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ObservableEventDispatcher
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /** @var array */
    private $dispatchedEvents = [];

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function dispatch(Event $event, string $eventName)
    {
        $this->dispatcher->dispatch($eventName, $event);
        $this->dispatchedEvents[] = $event;
    }

    /**
     * @return array
     */
    public function getDispatchedEvents(): array
    {
        return $this->dispatchedEvents;
    }
}
