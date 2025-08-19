<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class DeputyInvitedEvent extends Event
{
    public const NAME = 'deputy.invited';

    public function __construct(private readonly User $invitedDeputy)
    {
    }

    public function getInvitedDeputy(): User
    {
        return $this->invitedDeputy;
    }
}
