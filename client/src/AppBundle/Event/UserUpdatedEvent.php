<?php declare(strict_types=1);


namespace AppBundle\Event;

use AppBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserUpdatedEvent extends Event
{
    const NAME = 'user.updated';

    /** @var string */
    private $trigger;
    private $preUpdateEmail;
    private $postUpdateEmail;
    private $currentUserEmail;
    private $postUpdateFullName;
    private $preUpdateRoleName;
    private $postUpdateRoleName;

    /** @var User */
    private $postUpdateUser;
    private $preUpdateUser;

    public function __construct(User $preUpdateUser, User $postUpdateUser, User $currentUser, string $trigger)
    {
        $this->initialise($preUpdateUser, $postUpdateUser, $currentUser, $trigger);
    }

    private function initialise(User $preUpdateUser, User $postUpdateUser, User $currentUser, string $trigger)
    {
        $this->setCurrentUserEmail($currentUser->getEmail())
            ->setPostUpdateEmail($postUpdateUser->getEmail())
            ->setPostUpdateFullName($postUpdateUser->getFullName())
            ->setPostUpdateRoleName($postUpdateUser->getRoleName())
            ->setPreUpdateEmail($preUpdateUser->getEmail())
            ->setPreUpdateRoleName($preUpdateUser->getRoleName())
            ->setTrigger($trigger)
            ->setPreUpdateUser($preUpdateUser)
            ->setPostUpdateUser($postUpdateUser);
    }

    /**
     * @return string
     */
    public function getTrigger(): string
    {
        return $this->trigger;
    }

    /**
     * @param string $trigger
     * @return UserUpdatedEvent
     */
    public function setTrigger(string $trigger): UserUpdatedEvent
    {
        $this->trigger = $trigger;
        return $this;
    }

    /**
     * @return string
     */
    public function getPreUpdateEmail(): string
    {
        return $this->preUpdateEmail;
    }

    /**
     * @param string $preUpdateEmail
     * @return UserUpdatedEvent
     */
    public function setPreUpdateEmail(string $preUpdateEmail): UserUpdatedEvent
    {
        $this->preUpdateEmail = $preUpdateEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getPostUpdateEmail(): string
    {
        return $this->postUpdateEmail;
    }

    /**
     * @param string $postUpdateEmail
     * @return UserUpdatedEvent
     */
    public function setPostUpdateEmail(string $postUpdateEmail): UserUpdatedEvent
    {
        $this->postUpdateEmail = $postUpdateEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentUserEmail(): string
    {
        return $this->currentUserEmail;
    }

    /**
     * @param string $currentUserEmail
     * @return UserUpdatedEvent
     */
    public function setCurrentUserEmail(string $currentUserEmail): UserUpdatedEvent
    {
        $this->currentUserEmail = $currentUserEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getPostUpdateFullName(): string
    {
        return $this->postUpdateFullName;
    }

    /**
     * @param string $postUpdateFullName
     * @return UserUpdatedEvent
     */
    public function setPostUpdateFullName(string $postUpdateFullName): UserUpdatedEvent
    {
        $this->postUpdateFullName = $postUpdateFullName;
        return $this;
    }

    /**
     * @return string
     */
    public function getPreUpdateRoleName(): string
    {
        return $this->preUpdateRoleName;
    }

    /**
     * @param string $preUpdateRoleName
     * @return UserUpdatedEvent
     */
    public function setPreUpdateRoleName(string $preUpdateRoleName): UserUpdatedEvent
    {
        $this->preUpdateRoleName = $preUpdateRoleName;
        return $this;
    }

    /**
     * @return string
     */
    public function getPostUpdateRoleName(): string
    {
        return $this->postUpdateRoleName;
    }

    /**
     * @param string $postUpdateRoleName
     * @return UserUpdatedEvent
     */
    public function setPostUpdateRoleName(string $postUpdateRoleName): UserUpdatedEvent
    {
        $this->postUpdateRoleName = $postUpdateRoleName;
        return $this;
    }

    /**
     * @return User
     */
    public function getPostUpdateUser(): User
    {
        return $this->postUpdateUser;
    }

    /**
     * @param User $postUpdateUser
     * @return UserUpdatedEvent
     */
    public function setPostUpdateUser(User $postUpdateUser): UserUpdatedEvent
    {
        $this->postUpdateUser = $postUpdateUser;
        return $this;
    }

    /**
     * @return User
     */
    public function getPreUpdateUser()
    {
        return $this->preUpdateUser;
    }

    /**
     * @param mixed $preUpdateUser
     * @return UserUpdatedEvent
     */
    public function setPreUpdateUser($preUpdateUser)
    {
        $this->preUpdateUser = $preUpdateUser;
        return $this;
    }
}
