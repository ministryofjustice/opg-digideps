<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Client;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class ClientArchivedEvent extends Event
{
    public const NAME = 'client.archived';

    /** @var User */
    private $currentUser;

    /** @var Client */
    private $client;

    /** @var string */
    private $trigger;

    /**
     * ClientDeletedEvent constructor.
     */
    public function __construct(Client $client, User $currentUser, string $trigger)
    {
        $this->setClient($client);
        $this->setCurrentUser($currentUser);
        $this->setTrigger($trigger);
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function setTrigger(string $trigger): ClientArchivedEvent
    {
        $this->trigger = $trigger;

        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): ClientArchivedEvent
    {
        $this->client = $client;

        return $this;
    }

    public function getCurrentUser(): User
    {
        return $this->currentUser;
    }

    public function setCurrentUser(User $currentUser): ClientArchivedEvent
    {
        $this->currentUser = $currentUser;

        return $this;
    }
}
