<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class AdminUserCreatedEvent extends Event
{
    public const NAME = 'admin.user.created';

    private User $createdUser;
    private User $currentUser;
    private string $trigger;
    private string $roleType;

    private array $validRoleTypes = [
        User::ROLE_ADMIN_MANAGER,
        User::ROLE_ADMIN,
        User::ROLE_SUPER_ADMIN,
    ];

    public function __construct(string $trigger, User $createdUser, User $currentUser, string $roleType)
    {
        $this->createdUser = $createdUser;
        $this->setTrigger($trigger)
            ->setCurrentUser($currentUser)
            ->setRoleType($roleType);
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function setTrigger(string $trigger): AdminUserCreatedEvent
    {
        $this->trigger = $trigger;

        return $this;
    }

    public function getCurrentUser(): User
    {
        return $this->currentUser;
    }

    public function setCurrentUser(User $currentUser): AdminUserCreatedEvent
    {
        $this->currentUser = $currentUser;

        return $this;
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

    public function getRoleType(): string
    {
        return $this->roleType;
    }

    public function setRoleType(string $roleType): AdminUserCreatedEvent
    {
        if (in_array($roleType, $this->validRoleTypes)) {
            $this->roleType = $roleType;
        }

        return $this;
    }
}
