<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\User;

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
