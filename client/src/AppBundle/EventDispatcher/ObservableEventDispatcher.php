<?php declare(strict_types=1);


namespace AppBundle\EventDispatcher;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    public function dispatch(string $eventName, Event $event)
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
