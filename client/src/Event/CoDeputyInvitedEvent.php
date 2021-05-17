<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class CoDeputyInvitedEvent extends Event implements CoDeputyCreationEventInterface
{
    public const NAME = 'codeputy.invited';

    /** @var User */
    private $invitedCoDeputy;

    /** @var User */
    private $inviterDeputy;

    public function __construct(User $invitedCoDeputy, User $inviterDeputy)
    {
        $this->invitedCoDeputy = $invitedCoDeputy;
        $this->inviterDeputy = $inviterDeputy;
    }

    public function getInvitedCoDeputy(): User
    {
        return $this->invitedCoDeputy;
    }

    public function setInvitedCoDeputy(User $invitedCoDeputy): CoDeputyInvitedEvent
    {
        $this->invitedCoDeputy = $invitedCoDeputy;

        return $this;
    }

    public function getInviterDeputy(): User
    {
        return $this->inviterDeputy;
    }

    public function setInviterDeputy(User $inviterDeputy): CoDeputyInvitedEvent
    {
        $this->inviterDeputy = $inviterDeputy;

        return $this;
    }
}
