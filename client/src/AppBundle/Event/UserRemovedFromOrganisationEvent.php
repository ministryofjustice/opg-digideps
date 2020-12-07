<?php declare(strict_types=1);


namespace AppBundle\Event;

use AppBundle\Entity\Organisation;
use AppBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class UserRemovedFromOrganisationEvent extends Event
{
    const NAME = 'user.removed.from.organisation';

    private Organisation $organisation;
    private User $userToRemove;
    private User $currentUser;
    private string $trigger;

    public function __construct(Organisation $organisation, User $userToRemove, User $currentUser, string $trigger)
    {
        $this->organisation = $organisation;
        $this->userToRemove = $userToRemove;
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
    public function getUserToRemove(): User
    {
        return $this->userToRemove;
    }

    /**
     * @param User $userToRemove
     * @return UserRemovedFromOrganisationEvent
     */
    public function setUserToRemove(User $userToRemove): UserRemovedFromOrganisationEvent
    {
        $this->userToRemove = $userToRemove;
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
