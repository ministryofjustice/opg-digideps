<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Event;

use OPG\Digideps\Frontend\Entity\User;

interface CoDeputyCreationEventInterface
{
    /**
     * @return User
     */
    public function getInvitedCoDeputy();

    /**
     * @return self
     */
    public function setInvitedCoDeputy(User $invitedCoDeputy);

    /**
     * @return User
     */
    public function getInviterDeputy();

    /**
     * @return self
     */
    public function setInviterDeputy(User $inviterDeputy);
}
