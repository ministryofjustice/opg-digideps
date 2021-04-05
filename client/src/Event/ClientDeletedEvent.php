<?php declare(strict_types=1);


namespace App\Event;

use App\Entity\Client;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class ClientDeletedEvent extends Event
{
    const NAME = 'client.deleted';

    /** @var User */
    private $currentUser;

    /** @var Client */
    private $clientWithUsers;

    /** @var string */
    private $trigger;

    /**
     * ClientDeletedEvent constructor.
     * @param Client $clientWithUsers
     * @param User $currentUser
     * @param string $trigger
     */
    public function __construct(Client $clientWithUsers, User $currentUser, string $trigger)
    {
        $this->setClientWithUsers($clientWithUsers);
        $this->setCurrentUser($currentUser);
        $this->setTrigger($trigger);
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
     * @return ClientDeletedEvent
     */
    public function setTrigger(string $trigger): ClientDeletedEvent
    {
        $this->trigger = $trigger;
        return $this;
    }

    /**
     * @return Client
     */
    public function getClientWithUsers(): Client
    {
        return $this->clientWithUsers;
    }

    /**
     * @param Client $clientWithUsers
     * @return ClientDeletedEvent
     */
    public function setClientWithUsers(Client $clientWithUsers): ClientDeletedEvent
    {
        $this->clientWithUsers = $clientWithUsers;
        return $this;
    }

    /**
     * @return User
     */
    public function getCurrentUser(): User
    {
        return $this->currentUser;
    }

    /**
     * @param User $currentUser
     * @return ClientDeletedEvent
     */
    public function setCurrentUser(User $currentUser): ClientDeletedEvent
    {
        $this->currentUser = $currentUser;
        return $this;
    }
}
