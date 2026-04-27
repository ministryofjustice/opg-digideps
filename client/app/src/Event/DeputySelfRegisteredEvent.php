<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Event;

use OPG\Digideps\Frontend\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class DeputySelfRegisteredEvent extends Event
{
    public const string NAME = 'deputy.self.registered';

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
