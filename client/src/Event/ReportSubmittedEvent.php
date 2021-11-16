<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Report\Report;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class ReportSubmittedEvent extends Event
{
    public const NAME = 'report.submitted';

    /** @var Report */
    private $submittedReport;

    /** @var User */
    private $submittedBy;

    /** @var string|int|null */
    private $newYearReportId;

    public function __construct(Report $submittedReport, User $submittedBy, $newYearReportId)
    {
        $this->submittedReport = $submittedReport;
        $this->submittedBy = $submittedBy;
        $this->newYearReportId = $newYearReportId;
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
