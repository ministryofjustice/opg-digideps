<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class ChecklistsSynchronisedEvent extends Event
{
    public const NAME = 'checklists.synchronised';

    private array $reports;

    public function __construct(array $reports)
    {
        $this->setReports($reports);
    }

    public function getReports(): array
    {
        return $this->reports;
    }

    public function setReports(array $reports): ChecklistsSynchronisedEvent
    {
        $this->reports = $reports;

        return $this;
    }
}
