<?php declare(strict_types=1);


namespace AppBundle\Event;

use AppBundle\Entity\User;

interface CoDeputyCreationEventInterface
{
    /**
     * @return User
     */
    public function getInvitedCoDeputy();

    /**
     * @param User $invitedCoDeputy
     * @return self
     */
    public function setInvitedCoDeputy(User $invitedCoDeputy);

    /**
     * @return User
     */
    public function getInviterDeputy();

    /**
     * @param User $inviterDeputy
     * @return self
     */
    public function setInviterDeputy(User $inviterDeputy);
}
