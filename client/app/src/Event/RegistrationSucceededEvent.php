<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class RegistrationSucceededEvent extends Event
{
    public const NAME = 'registration.succeeded';

    public function __construct(private User $registeredDeputy)
    {
        $this->setRegisteredDeputy($this->registeredDeputy);
    }

    public function getRegisteredDeputy(): User
    {
        return $this->registeredDeputy;
    }

    public function setRegisteredDeputy(User $registeredDeputy): self
    {
        $this->registeredDeputy = $registeredDeputy;

        return $this;
    }
}
