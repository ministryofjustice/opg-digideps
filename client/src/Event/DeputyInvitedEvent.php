<?php declare(strict_types=1);


namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class DeputyInvitedEvent extends Event
{
    public const NAME = 'deputy.invited';

    /** @var User */
    private $invitedDeputy;

    public function __construct(User $invitedDeputy)
    {
        $this->invitedDeputy = $invitedDeputy;
    }

    /**
     * @return User
     */
    public function getInvitedDeputy(): User
    {
        return $this->invitedDeputy;
    }

    /**
     * @param User $invitedDeputy
     * @return DeputyInvitedEvent
     */
    public function setInvitedDeputy(User $invitedDeputy): DeputyInvitedEvent
    {
        $this->invitedDeputy = $invitedDeputy;
        return $this;
    }
}
