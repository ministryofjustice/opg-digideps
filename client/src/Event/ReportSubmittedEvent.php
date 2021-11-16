<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Report\Report;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class ReportSubmittedEvent extends Event
{
    public const NAME = 'report.submitted';

    public function __construct(private Report $submittedReport, private User $submittedBy, private $newYearReportId)
    {
    }

    public function getSubmittedReport(): Report
    {
        return $this->submittedReport;
    }

    public function setSubmittedReport(Report $submittedReport): ReportSubmittedEvent
    {
        $this->submittedReport = $submittedReport;

        return $this;
    }

    public function getSubmittedBy(): User
    {
        return $this->submittedBy;
    }

    public function setSubmittedBy(User $submittedBy): ReportSubmittedEvent
    {
        $this->submittedBy = $submittedBy;

        return $this;
    }

    /**
     * @return string|int|null
     */
    public function getNewYearReportId()
    {
        return $this->newYearReportId;
    }

    /**
     * @param string|int|null $newYearReportId
     */
    public function setNewYearReportId($newYearReportId): ReportSubmittedEvent
    {
        $this->newYearReportId = $newYearReportId;

        return $this;
    }
}
