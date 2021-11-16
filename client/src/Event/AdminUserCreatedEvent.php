<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class AdminUserCreatedEvent extends Event
{
    public const NAME = 'admin.user.created';

    public function __construct(private User $createdUser)
    {
    }

    public function getCreatedUser(): User
    {
        return $this->createdUser;
    }

    public function setCreatedUser(User $createdUser): AdminUserCreatedEvent
    {
        $this->createdUser = $createdUser;

        return $this;
    }
}
