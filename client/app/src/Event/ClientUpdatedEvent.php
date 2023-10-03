<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Client;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class ClientUpdatedEvent extends Event
{
    public const NAME = 'client.updated';

    /** @var Client */
    private $preUpdateClient;
    private $postUpdateClient;

    /** @var User */
    private $changedBy;

    /** @var string */
    private $trigger;

    public function __construct(
        Client $preUpdateClient,
        Client $postUpdateClient,
        User $changedBy,
        string $trigger
    ) {
        $this->setPreUpdateClient($preUpdateClient);
        $this->setPostUpdateClient($postUpdateClient);
        $this->setChangedBy($changedBy);
        $this->setTrigger($trigger);
    }

    public function getPreUpdateClient(): Client
    {
        return $this->preUpdateClient;
    }

    public function setPreUpdateClient(Client $preUpdateClient): ClientUpdatedEvent
    {
        $this->preUpdateClient = $preUpdateClient;

        return $this;
    }

    public function getPostUpdateClient(): Client
    {
        return $this->postUpdateClient;
    }

    public function setPostUpdateClient(Client $postUpdateClient): ClientUpdatedEvent
    {
        $this->postUpdateClient = $postUpdateClient;

        return $this;
    }

    public function getChangedBy(): User
    {
        return $this->changedBy;
    }

    public function setChangedBy(User $changedBy): ClientUpdatedEvent
    {
        $this->changedBy = $changedBy;

        return $this;
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function setTrigger(string $trigger): ClientUpdatedEvent
    {
        $this->trigger = $trigger;

        return $this;
    }
}
