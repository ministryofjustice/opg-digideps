<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Client;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class DeputyChangedOrgEvent extends Event
{
    const NAME = 'deputy.changedOrg';

    private string $trigger;
    private User $preUpdateDeputy;
    private User $postUpdateDeputy;
    private Client $preUpdateClient;
    private Client $postUpdateClient;


    public function __construct(
        string $trigger,
        Client $preUpdateClient,
        Client $postUpdateClient,
        User $preUpdateDeputy,
        User $postUpdateDeputy,
    )
    {
        $this->setTrigger($trigger);
        $this->setPreUpdateDeputy($preUpdateDeputy);
        $this->setPostUpdateDeputy($postUpdateDeputy);
        $this->setPreUpdateClient($preUpdateClient);
        $this->setPostUpdateClient($postUpdateClient);
    }


    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function setTrigger(string $trigger) : DeputyChangedOrgEvent
    {
        $this->trigger = $trigger;

        return $this;
    }

    public function getPreUpdateDeputy(): User
    {
        return $this->preUpdateDeputy;
    }

    public function setPreUpdateDeputy(User $preUpdateDeputy): DeputyChangedOrgEvent
    {
        $this->preUpdateDeputy = $preUpdateDeputy;

        return $this;
    }

    public function getPostUpdateDeputy(): User
    {
        return $this->postUpdateDeputy;
    }

    public function setPostUpdateDeputy(User $postUpdateDeputy): DeputyChangedOrgEvent
    {
        $this->postUpdateDeputy = $postUpdateDeputy;

        return $this;
    }


    public function getPreUpdateClient(): Client
    {
        return $this->preUpdateClient;
    }

    public function setPreUpdateClient(Client $preUpdateClient): DeputyChangedOrgEvent
    {
        $this->preUpdateClient = $preUpdateClient;

        return $this;
    }

    public function getPostUpdateClient(): Client
    {
        return $this->postUpdateClient;
    }

    public function setPostUpdateClient(Client $postUpdateClient): DeputyChangedOrgEvent
    {
        $this->postUpdateClient = $postUpdateClient;

        return $this;
    }

}
