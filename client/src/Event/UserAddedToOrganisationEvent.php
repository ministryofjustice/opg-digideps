<?php declare(strict_types=1);


namespace App\Event;

use App\Entity\Organisation;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserAddedToOrganisationEvent extends Event
{
    const NAME = 'user.added.to.organisation';

    private Organisation $organisation;
    private User $addedUser;
    private User $currentUser;
    private string $trigger;

    public function __construct(Organisation $organisation, User $addedUser, User $currentUser, string $trigger)
    {
        $this->organisation = $organisation;
        $this->addedUser = $addedUser;
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
     * @return UserAddedToOrganisationEvent
     */
    public function setOrganisation(Organisation $organisation): UserAddedToOrganisationEvent
    {
        $this->organisation = $organisation;
        return $this;
    }

    /**
     * @return User
     */
    public function getAddedUser(): User
    {
        return $this->addedUser;
    }

    /**
     * @param User $addedUser
     * @return UserAddedToOrganisationEvent
     */
    public function setAddedUser(User $addedUser): UserAddedToOrganisationEvent
    {
        $this->addedUser = $addedUser;
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
     * @return UserAddedToOrganisationEvent
     */
    public function setCurrentUser(User $currentUser): UserAddedToOrganisationEvent
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
     * @return UserAddedToOrganisationEvent
     */
    public function setTrigger(string $trigger): UserAddedToOrganisationEvent
    {
        $this->trigger = $trigger;
        return $this;
    }
}
