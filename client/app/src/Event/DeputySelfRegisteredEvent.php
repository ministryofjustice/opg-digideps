<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class DeputySelfRegisteredEvent extends Event
{
    public const NAME = 'deputy.self.registered';

    /** @var User */
    private $registeredDeputy;

    public function __construct(User $registeredDeputy)
    {
        $this->registeredDeputy = $registeredDeputy;
    }

    public function getRegisteredDeputy(): User
    {
        return $this->registeredDeputy;
    }

    public function setRegisteredDeputy(User $registeredDeputy): DeputySelfRegisteredEvent
    {
        $this->registeredDeputy = $registeredDeputy;

        return $this;
    }
}
