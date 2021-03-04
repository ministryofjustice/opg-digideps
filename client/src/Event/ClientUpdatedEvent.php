<?php declare(strict_types=1);


namespace App\Event;

use App\Entity\Client;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class ClientUpdatedEvent extends Event
{
    public const NAME = 'client.updated';

    /** @var Client */
    private $preUpdateClient;
    private $postUpdateClient;

    /** @var User */
    private $changedBy;

    /** @var string */
    private $trigger;

    public function __construct(
        Client $preUpdateClient,
        Client $postUpdateClient,
        User $changedBy,
        string $trigger
    ) {
        $this->setPreUpdateClient($preUpdateClient);
        $this->setPostUpdateClient($postUpdateClient);
        $this->setChangedBy($changedBy);
        $this->setTrigger($trigger);
    }

    /**
     * @return Client
     */
    public function getPreUpdateClient(): Client
    {
        return $this->preUpdateClient;
    }

    /**
     * @param Client $preUpdateClient
     * @return ClientUpdatedEvent
     */
    public function setPreUpdateClient(Client $preUpdateClient): ClientUpdatedEvent
    {
        $this->preUpdateClient = $preUpdateClient;
        return $this;
    }

    /**
     * @return Client
     */
    public function getPostUpdateClient(): Client
    {
        return $this->postUpdateClient;
    }

    /**
     * @param Client $postUpdateClient
     * @return ClientUpdatedEvent
     */
    public function setPostUpdateClient(Client $postUpdateClient): ClientUpdatedEvent
    {
        $this->postUpdateClient = $postUpdateClient;
        return $this;
    }

    /**
     * @return User
     */
    public function getChangedBy(): User
    {
        return $this->changedBy;
    }

    /**
     * @param User $changedBy
     * @return ClientUpdatedEvent
     */
    public function setChangedBy(User $changedBy): ClientUpdatedEvent
    {
        $this->changedBy = $changedBy;
        return $this;
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
     * @return ClientUpdatedEvent
     */
    public function setTrigger(string $trigger): ClientUpdatedEvent
    {
        $this->trigger = $trigger;
        return $this;
    }
}
