<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class AdminManagerCreatedEvent extends Event
{
    public const NAME = 'admin.manager.created';

    private User $createdAdminManager;
    private User $currentUser;
    private string $trigger;

    public function __construct(string $trigger, User $currentUser, User $createdAdminManager, string $roleType)
    {
        $this->setcreatedAdminManager($createdAdminManager)
            ->setTrigger($trigger)
            ->setCurrentUser($currentUser)
            ->setRoleType($roleType);
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function setTrigger(string $trigger): AdminManagerCreatedEvent
    {
        $this->trigger = $trigger;

        return $this;
    }

    public function getCurrentUser(): User
    {
        return $this->currentUser;
    }

    public function setCurrentUser(User $currentUser): AdminManagerCreatedEvent
    {
        $this->currentUser = $currentUser;

        return $this;
    }

    public function getCreatedAdminManager(): User
    {
        return $this->createdAdminManager;
    }

    public function setCreatedAdminManager(User $createdAdminManager): AdminManagerCreatedEvent
    {
        $this->createdAdminManager = $createdAdminManager;

        return $this;
    }
}
