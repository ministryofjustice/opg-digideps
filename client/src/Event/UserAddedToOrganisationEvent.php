<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Organisation;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserAddedToOrganisationEvent extends Event
{
    const NAME = 'user.added.to.organisation';

    public function __construct(private Organisation $organisation, private User $addedUser, private User $currentUser, private string $trigger)
    {
    }

    public function getOrganisation(): Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(Organisation $organisation): UserAddedToOrganisationEvent
    {
        $this->organisation = $organisation;

        return $this;
    }

    public function getAddedUser(): User
    {
        return $this->addedUser;
    }

    public function setAddedUser(User $addedUser): UserAddedToOrganisationEvent
    {
        $this->addedUser = $addedUser;

        return $this;
    }

    public function getCurrentUser(): User
    {
        return $this->currentUser;
    }

    public function setCurrentUser(User $currentUser): UserAddedToOrganisationEvent
    {
        $this->currentUser = $currentUser;

        return $this;
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function setTrigger(string $trigger): UserAddedToOrganisationEvent
    {
        $this->trigger = $trigger;

        return $this;
    }
}
