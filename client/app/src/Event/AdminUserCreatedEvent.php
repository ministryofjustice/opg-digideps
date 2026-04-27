<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Event;

use OPG\Digideps\Frontend\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class AdminUserCreatedEvent extends Event
{
    public const string NAME = 'admin.user.created';

    /** @var User */
    private $createdUser;

    public function __construct(User $createdUser)
    {
        $this->createdUser = $createdUser;
    }

    public function getCreatedUser(): User
    {
        return $this->createdUser;
    }

    public function setCreatedUser(User $createdUser): AdminUserCreatedEvent
    {
        $this->createdUser = $createdUser;

        return $this;
    }
}
