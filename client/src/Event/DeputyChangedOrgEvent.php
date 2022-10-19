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


    public function __construct(string $trigger, int $previousOrg, int $newOrg)
    {
        $this->setTrigger($trigger);
        $this->setPreviousOrg($previousOrg);
        $this->setNewOrg($newOrg);
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

}
