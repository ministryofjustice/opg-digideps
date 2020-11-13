<?php declare(strict_types=1);


namespace AppBundle\Event;

use AppBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class UserCreatedEvent extends Event
{
    public const NAME = 'user.created';

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
     * @return UserCreatedEvent
     */
    public function setCreatedUser(User $createdUser): UserCreatedEvent
    {
        $this->createdUser = $createdUser;
        return $this;
    }
}
