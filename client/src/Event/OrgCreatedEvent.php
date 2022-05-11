<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class OrgCreatedEvent extends Event
{
    public const NAME = 'org.created';

    private string $trigger;
    private User $currentUser;
    private array $organisation;

    public function __construct(string $trigger, User $currentUser, array $organisation)
    {
        $this->setTrigger($trigger);
        $this->setCurrentUser($currentUser);
        $this->setOrganisation($organisation);
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function setTrigger(string $trigger): OrgCreatedEvent
    {
        $this->trigger = $trigger;

        return $this;
    }

    public function getCurrentUser(): User
    {
        return $this->currentUser;
    }

    public function setCurrentUser(User $currentUser): OrgCreatedEvent
    {
        $this->currentUser = $currentUser;

        return $this;
    }

    public function getOrganisation(): array
    {
        return $this->organisation;
    }

    public function setOrganisation(array $organisation): OrgCreatedEvent
    {
        $this->organisation = $organisation;

        return $this;
    }
}
