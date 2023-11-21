<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserRetentionPolicyCommandEvent extends Event
{
    public const NAME = 'user.deleted';

    public function __construct(private User $deletedAdminUser, private string $trigger)
    {
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function setTrigger(string $trigger): UserRetentionPolicyCommandEvent
    {
        $this->trigger = $trigger;

        return $this;
    }

    public function getDeletedAdminUser(): User
    {
        return $this->deletedAdminUser;
    }

    public function setDeletedAdminUser(User $deletedAdminUser): UserRetentionPolicyCommandEvent
    {
        $this->deletedAdminUser = $deletedAdminUser;

        return $this;
    }
}
