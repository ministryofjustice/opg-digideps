<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\CasRec;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class CSVUploadedEvent extends Event
{
    const NAME = 'csv.uploaded';

    private string $trigger;
    private string $source;
    private string $roleType;

    private array $validRoleTypes = [
        User::TYPE_LAY,
        User::TYPE_PA,
        User::TYPE_PROF,
    ];

    private array $validSources = [
        Casrec::CASREC_SOURCE,
        CasRec::SIRIUS_SOURCE,
    ];

    public function __construct(string $source, $roleType, $trigger)
    {
        $this->setTrigger($trigger)
            ->setSource($source)
            ->setRoleType($roleType);
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function setTrigger(string $trigger): CSVUploadedEvent
    {
        $this->trigger = $trigger;

        return $this;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): CSVUploadedEvent
    {
        if (in_array($source, $this->validSources)) {
            $this->source = $source;
        }

        return $this;
    }

    public function getRoleType()
    {
        return $this->roleType;
    }

    public function setRoleType(string $roleType): CSVUploadedEvent
    {
        if (in_array($roleType, $this->validRoleTypes)) {
            $this->roleType = $roleType;
        }

        return $this;
    }
}
