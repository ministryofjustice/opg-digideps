<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Client;
use Symfony\Contracts\EventDispatcher\Event;

class DeputyChangedOrgEvent extends Event
{
    const NAME = 'deputy.changedOrg';

    private string $trigger;
    private Client $client;
    private Client $previousDeputyOrg;


    public function __construct(string $trigger, Client $previousDeputyOrg, Client $client)
    {
        $this->setTrigger($trigger);
        $this->setPreviousDeputyOrg($previousDeputyOrg);
        $this->setClient($client);
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

    public function getPreviousDeputyOrg(): Client
    {
        return $this->previousDeputyOrg;
    }

    public function setPreviousDeputyOrg(Client $previousDeputyOrg): DeputyChangedOrgEvent
    {
        $this->previousDeputyOrg = $previousDeputyOrg;

        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): DeputyChangedOrgEvent
    {
        $this->client = $client;

        return $this;
    }

}
