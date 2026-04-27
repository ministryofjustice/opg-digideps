<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Event;

use OPG\Digideps\Frontend\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class RegistrationSucceededEvent extends Event
{
    public const string DEPUTY = 'deputy.registration.succeeded';

    public const string ADMIN = 'admin.registration.succeeded';

    public function __construct(private User $registeredUser)
    {
        $this->setRegisteredUser($this->registeredUser);
    }

    public function getRegisteredUser(): User
    {
        return $this->registeredUser;
    }

    public function setRegisteredUser(User $registeredUser): self
    {
        $this->registeredUser = $registeredUser;

        return $this;
    }
}
