<?php declare(strict_types=1);


namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class CoDeputyCreatedEvent extends Event implements CoDeputyCreationEventInterface
{
    public const NAME = 'codeputy.created';

    /** @var User */
    private $invitedCoDeputy;

    /** @var User */
    private $inviterDeputy;

    public function __construct(User $invitedCoDeputy, User $inviterDeputy)
    {
        $this->invitedCoDeputy = $invitedCoDeputy;
        $this->inviterDeputy = $inviterDeputy;
    }

    /**
     * @return User
     */
    public function getInvitedCoDeputy(): User
    {
        return $this->invitedCoDeputy;
    }

    /**
     * @param User $invitedCoDeputy
     * @return CoDeputyCreatedEvent
     */
    public function setInvitedCoDeputy(User $invitedCoDeputy): CoDeputyCreatedEvent
    {
        $this->invitedCoDeputy = $invitedCoDeputy;
        return $this;
    }

    /**
     * @return User
     */
    public function getInviterDeputy(): User
    {
        return $this->inviterDeputy;
    }

    /**
     * @param User $inviterDeputy
     * @return CoDeputyCreatedEvent
     */
    public function setInviterDeputy(User $inviterDeputy): CoDeputyCreatedEvent
    {
        $this->inviterDeputy = $inviterDeputy;
        return $this;
    }
}
