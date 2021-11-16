<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserPasswordResetEvent extends Event
{
    public const NAME = 'password.reset';

    public function __construct(private User $passwordResetUser)
    {
    }

    public function getPasswordResetUser(): User
    {
        return $this->passwordResetUser;
    }

    public function setPasswordResetUser(User $passwordResetUser): UserPasswordResetEvent
    {
        $this->passwordResetUser = $passwordResetUser;

        return $this;
    }
}
