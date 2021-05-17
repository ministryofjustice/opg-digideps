<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserUpdatedEvent extends Event
{
    const NAME = 'user.updated';

    /** @var string */
    private $trigger;

    /** @var User */
    private $postUpdateUser;
    private $preUpdateUser;
    private $currentUser;

    public function __construct(User $preUpdateUser, User $postUpdateUser, User $currentUser, string $trigger)
    {
        $this->setTrigger($trigger)
            ->setPreUpdateUser($preUpdateUser)
            ->setPostUpdateUser($postUpdateUser)
            ->setCurrentUser($currentUser);
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function setTrigger(string $trigger): UserUpdatedEvent
    {
        $this->trigger = $trigger;

        return $this;
    }

    public function getPostUpdateUser(): User
    {
        return $this->postUpdateUser;
    }

    public function setPostUpdateUser(User $postUpdateUser): UserUpdatedEvent
    {
        $this->postUpdateUser = $postUpdateUser;

        return $this;
    }

    /**
     * @return User
     */
    public function getPreUpdateUser()
    {
        return $this->preUpdateUser;
    }

    /**
     * @param mixed $preUpdateUser
     *
     * @return UserUpdatedEvent
     */
    public function setPreUpdateUser($preUpdateUser)
    {
        $this->preUpdateUser = $preUpdateUser;

        return $this;
    }

    public function getCurrentUser(): User
    {
        return $this->currentUser;
    }

    public function setCurrentUser(User $currentUser): UserUpdatedEvent
    {
        $this->currentUser = $currentUser;

        return $this;
    }
}
