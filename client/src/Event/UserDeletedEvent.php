<?php declare(strict_types=1);


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

    /**
     * @return User
     */
    public function getDeletedUser(): User
    {
        return $this->deletedUser;
    }

    /**
     * @param User $deletedUser
     * @return UserDeletedEvent
     */
    public function setDeletedUser(User $deletedUser): UserDeletedEvent
    {
        $this->deletedUser = $deletedUser;
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
     * @return UserDeletedEvent
     */
    public function setTrigger(string $trigger): UserDeletedEvent
    {
        $this->trigger = $trigger;
        return $this;
    }

    /**
     * @return User
     */
    public function getDeletedBy(): User
    {
        return $this->deletedBy;
    }

    /**
     * @param User $deletedBy
     * @return UserDeletedEvent
     */
    public function setDeletedBy(User $deletedBy): UserDeletedEvent
    {
        $this->deletedBy = $deletedBy;
        return $this;
    }
}
