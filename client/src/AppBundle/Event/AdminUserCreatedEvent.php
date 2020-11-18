<?php declare(strict_types=1);


namespace AppBundle\Event;

use AppBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class AdminUserCreatedEvent extends Event
{
    public const NAME = 'admin.user.created';

    /**  @var User */
    private $createdUser;

    public function __construct(User $createdUser)
    {
        $this->createdUser = $createdUser;
    }

    /**
     * @return User
     */
    public function getCreatedUser(): User
    {
        return $this->createdUser;
    }

    /**
     * @param User $createdUser
     * @return AdminUserCreatedEvent
     */
    public function setCreatedUser(User $createdUser): AdminUserCreatedEvent
    {
        $this->createdUser = $createdUser;
        return $this;
    }
}
