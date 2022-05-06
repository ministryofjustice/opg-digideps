<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Organisation;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class OrgCreatedEvent extends Event
{
    public const NAME = 'org.created';

    private string $trigger;
    private User $currentUser;
    private Organisation $organisation;

    public function __construct(string $trigger, User $currentUser, Organisation $organisation)
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

    public function getOrganisation(): Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(Organisation $organisation): OrgCreatedEvent
    {
        $this->organisation = $organisation;

        return $this;
    }
}
