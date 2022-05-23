<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class CSVUploadedEvent extends Event
{
    const NAME = 'csv.uploaded';

    private string $trigger;
    private string $roleType;

    private array $validRoleTypes = [
        User::TYPE_LAY,
        'ORG',
    ];

    public function __construct($roleType, $trigger)
    {
        $this->setTrigger($trigger)
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

    public function getRoleType(): string
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
