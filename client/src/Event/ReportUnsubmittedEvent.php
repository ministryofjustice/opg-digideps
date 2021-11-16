<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Report\Report;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class ReportUnsubmittedEvent extends Event
{
    public const NAME = 'report.unsubmitted';

    public function __construct(private Report $unsubmittedReport, private User $unsubmittedBy, private string $trigger)
    {
    }

    public function getTrigger()
    {
        return $this->trigger;
    }

    public function getUnsubmittedReport(): Report
    {
        return $this->unsubmittedReport;
    }

    public function setUnsubmittedReport(Report $unsubmittedReport): ReportUnsubmittedEvent
    {
        $this->unsubmittedReport = $unsubmittedReport;

        return $this;
    }

    public function getUnsubmittedBy(): User
    {
        return $this->unsubmittedBy;
    }

    public function setUnsubmittedBy(User $unsubmittedBy): ReportUnsubmittedEvent
    {
        $this->unsubmittedBy = $unsubmittedBy;

        return $this;
    }
}
