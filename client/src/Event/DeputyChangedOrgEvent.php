<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class DeputyChangedOrgEvent extends Event
{
    const NAME = 'deputy.changedOrg';

    private string $trigger;
    private int $previousOrg;
    private int $newOrg;
    private array $client;


    public function __construct(string $trigger, int $previousOrg, int $newOrg, array $client)
    {
        $this->setTrigger($trigger);
        $this->setPreviousOrg($previousOrg);
        $this->setNewOrg($newOrg);
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

    public function getPreviousOrg(): int
    {
        return $this->previousOrg;
    }

    public function setPreviousOrg(int $previousOrg): DeputyChangedOrgEvent
    {
        $this->previousOrg = $previousOrg;

        return $this;
    }

    public function getNewOrg(): int
    {
        return $this->newOrg;
    }

    public function setNewOrg(int $newOrg): DeputyChangedOrgEvent
    {
        $this->newOrg = $newOrg;

        return $this;
    }

    public function getClient(): array
    {
        return $this->client;
    }

    public function setClient(array $client): DeputyChangedOrgEvent
    {
        $this->client = $client;

        return $this;
    }

}
