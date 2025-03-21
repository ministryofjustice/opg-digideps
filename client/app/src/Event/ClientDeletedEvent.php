<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Client;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ClientDeletedEvent extends Event
{
    public const NAME = 'client.deleted';

    /** @var User */
    private $currentUser;

    /** @var Client */
    private $clientWithUsers;

    /** @var string */
    private $trigger;

    /**
     * ClientDeletedEvent constructor.
     */
    public function __construct(Client $clientWithUsers, ?UserInterface $currentUser, string $trigger)
    {
        $this->setClientWithUsers($clientWithUsers);
        $this->setCurrentUser($currentUser);
        $this->setTrigger($trigger);
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function setTrigger(string $trigger): ClientDeletedEvent
    {
        $this->trigger = $trigger;

        return $this;
    }

    public function getClientWithUsers(): Client
    {
        return $this->clientWithUsers;
    }

    public function setClientWithUsers(Client $clientWithUsers): ClientDeletedEvent
    {
        $this->clientWithUsers = $clientWithUsers;

        return $this;
    }

    public function getCurrentUser(): User
    {
        return $this->currentUser;
    }

    public function setCurrentUser(?UserInterface $currentUser): ClientDeletedEvent
    {
        $this->currentUser = $currentUser;

        return $this;
    }
}
