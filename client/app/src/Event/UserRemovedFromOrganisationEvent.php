<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Event;

use OPG\Digideps\Frontend\Entity\Organisation;
use OPG\Digideps\Frontend\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserRemovedFromOrganisationEvent extends Event
{
    public const string NAME = 'user.removed.from.organisation';

    private Organisation $organisation;
    private User $removedUser;
    private User $currentUser;
    private string $trigger;

    public function __construct(Organisation $organisation, User $userToRemove, User $currentUser, string $trigger)
    {
        $this->organisation = $organisation;
        $this->removedUser = $userToRemove;
        $this->currentUser = $currentUser;
        $this->trigger = $trigger;
    }

    public function getOrganisation(): Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(Organisation $organisation): UserRemovedFromOrganisationEvent
    {
        $this->organisation = $organisation;

        return $this;
    }

    public function getRemovedUser(): User
    {
        return $this->removedUser;
    }

    public function setRemovedUser(User $removedUser): UserRemovedFromOrganisationEvent
    {
        $this->removedUser = $removedUser;

        return $this;
    }

    public function getCurrentUser(): User
    {
        return $this->currentUser;
    }

    public function setCurrentUser(User $currentUser): UserRemovedFromOrganisationEvent
    {
        $this->currentUser = $currentUser;

        return $this;
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function setTrigger(string $trigger): UserRemovedFromOrganisationEvent
    {
        $this->trigger = $trigger;

        return $this;
    }
}
