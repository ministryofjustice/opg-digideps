<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserDeletedEvent extends Event
{
    public const NAME = 'user.deleted';

    /** @var User */
    private $deletedUser;
    private $deletedBy;

    /** @var string */
    private $trigger;

    public function __construct(User $deletedUser, User $deletedBy, string $trigger)
    {
        $this->setDeletedUser($deletedUser);
        $this->setTrigger($trigger);
        $this->setDeletedBy($deletedBy);
    }

    public function getDeletedUser(): User
    {
        return $this->deletedUser;
    }

    public function setDeletedUser(User $deletedUser): UserDeletedEvent
    {
        $this->deletedUser = $deletedUser;

        return $this;
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function setTrigger(string $trigger): UserDeletedEvent
    {
        $this->trigger = $trigger;

        return $this;
    }

    public function getDeletedBy(): User
    {
        return $this->deletedBy;
    }

    public function setDeletedBy(User $deletedBy): UserDeletedEvent
    {
        $this->deletedBy = $deletedBy;

        return $this;
    }
}
