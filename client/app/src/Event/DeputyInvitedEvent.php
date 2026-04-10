<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Event;

use OPG\Digideps\Frontend\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class DeputyInvitedEvent extends Event
{
    public const string NAME = 'deputy.invited';

    public function __construct(private readonly User $invitedDeputy)
    {
    }

    public function getInvitedDeputy(): User
    {
        return $this->invitedDeputy;
    }
}
