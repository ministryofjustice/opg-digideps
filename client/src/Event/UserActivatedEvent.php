<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserActivatedEvent extends Event
{
    public const NAME = 'user.activated';

    public function __construct(private User $activatedUser)
    {
    }

    public function getActivatedUser(): User
    {
        return $this->activatedUser;
    }

    public function setActivatedUser(User $activatedUser): UserActivatedEvent
    {
        $this->activatedUser = $activatedUser;

        return $this;
    }
}
