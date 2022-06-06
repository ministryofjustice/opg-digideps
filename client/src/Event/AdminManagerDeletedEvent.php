<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class AdminManagerDeletedEvent extends Event
{
    public const NAME = 'admin.manager.deleted';

    private User $deletedAdminManager;
    private User $currentUser;
    private string $trigger;

    public function __construct(string $trigger, User $currentUser, User $deletedAdminManager)
    {
        $this->setTrigger($trigger)
            ->setCurrentUser($currentUser)
            ->setDeletedAdminManager($deletedAdminManager);
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function setTrigger(string $trigger): AdminManagerDeletedEvent
    {
        $this->trigger = $trigger;

        return $this;
    }

    public function getCurrentUser(): User
    {
        return $this->currentUser;
    }

    public function setCurrentUser(User $currentUser): AdminManagerDeletedEvent
    {
        $this->currentUser = $currentUser;

        return $this;
    }

    public function getDeletedAdminManager(): User
    {
        return $this->deletedAdminManager;
    }

    public function setDeletedAdminManager(User $deletedAdminManager): AdminManagerDeletedEvent
    {
        $this->deletedAdminManager = $deletedAdminManager;

        return $this;
    }
}
