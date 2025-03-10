<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class DeputyChangedOrgEvent extends Event
{
    const NAME = 'deputy.changedOrg';

    public function __construct(private string $trigger, private int $deputyId, private int $previousOrgId, private int $newOrgId, private int $clientId)
    {
        $this->setTrigger($trigger);
        $this->setDeputyId($deputyId);
        $this->setPreviousOrgId($previousOrgId);
        $this->setNewOrgId($newOrgId);
        $this->setClientId($clientId);
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

    public function getPreviousOrgId(): int
    {
        return $this->previousOrgId;
    }

    public function setPreviousOrgId(int $previousOrgId): DeputyChangedOrgEvent
    {
        $this->previousOrgId = $previousOrgId;

        return $this;
    }

    public function getDeputyId(): int
    {
        return $this->deputyId;
    }

    public function setDeputyId(int $deputyId): DeputyChangedOrgEvent
    {
        $this->deputyId = $deputyId;

        return $this;
    }

    public function getNewOrgId(): int
    {
        return $this->newOrgId;
    }

    public function setNewOrgId(int $newOrgId): DeputyChangedOrgEvent
    {
        $this->newOrgId = $newOrgId;

        return $this;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function setClientId(int $clientId): DeputyChangedOrgEvent
    {
        $this->clientId = $clientId;

        return $this;
    }

}
