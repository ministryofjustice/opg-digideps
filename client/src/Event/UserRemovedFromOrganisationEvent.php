<?php declare(strict_types=1);


namespace App\Event;

use App\Entity\Organisation;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserRemovedFromOrganisationEvent extends Event
{
    const NAME = 'user.removed.from.organisation';

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

    /**
     * @return Organisation
     */
    public function getOrganisation(): Organisation
    {
        return $this->organisation;
    }

    /**
     * @param Organisation $organisation
     * @return UserRemovedFromOrganisationEvent
     */
    public function setOrganisation(Organisation $organisation): UserRemovedFromOrganisationEvent
    {
        $this->organisation = $organisation;
        return $this;
    }

    /**
     * @return User
     */
    public function getRemovedUser(): User
    {
        return $this->removedUser;
    }

    /**
     * @param User $removedUser
     * @return UserRemovedFromOrganisationEvent
     */
    public function setRemovedUser(User $removedUser): UserRemovedFromOrganisationEvent
    {
        $this->removedUser = $removedUser;
        return $this;
    }

    /**
     * @return User
     */
    public function getCurrentUser(): User
    {
        return $this->currentUser;
    }

    /**
     * @param User $currentUser
     * @return UserRemovedFromOrganisationEvent
     */
    public function setCurrentUser(User $currentUser): UserRemovedFromOrganisationEvent
    {
        $this->currentUser = $currentUser;
        return $this;
    }

    /**
     * @return string
     */
    public function getTrigger(): string
    {
        return $this->trigger;
    }

    /**
     * @param string $trigger
     * @return UserRemovedFromOrganisationEvent
     */
    public function setTrigger(string $trigger): UserRemovedFromOrganisationEvent
    {
        $this->trigger = $trigger;
        return $this;
    }
}
