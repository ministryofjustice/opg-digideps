<?php declare(strict_types=1);


namespace AppBundle\Event;

use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class ReportSubmittedEvent extends Event
{
    public const NAME = 'report.submitted';

    /** @var Report */
    private $submittedReport;

    /** @var User */
    private $submittedBy;

    /** @var string|null */
    private $newYearReportId;

    public function __construct(Report $submittedReport, User $submittedBy, ?string $newYearReportId)
    {
        $this->submittedReport = $submittedReport;
        $this->submittedBy = $submittedBy;
        $this->newYearReportId = $newYearReportId;
    }

    /**
     * @return Report
     */
    public function getSubmittedReport(): Report
    {
        return $this->submittedReport;
    }

    /**
     * @param Report $submittedReport
     * @return ReportSubmittedEvent
     */
    public function setSubmittedReport(Report $submittedReport): ReportSubmittedEvent
    {
        $this->submittedReport = $submittedReport;
        return $this;
    }

    /**
     * @return User
     */
    public function getSubmittedBy(): User
    {
        return $this->submittedBy;
    }

    /**
     * @param User $submittedBy
     * @return ReportSubmittedEvent
     */
    public function setSubmittedBy(User $submittedBy): ReportSubmittedEvent
    {
        $this->submittedBy = $submittedBy;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNewYearReportId(): ?string
    {
        return $this->newYearReportId;
    }

    /**
     * @param string|null $newYearReportId
     * @return ReportSubmittedEvent
     */
    public function setNewYearReportId(?string $newYearReportId): ReportSubmittedEvent
    {
        $this->newYearReportId = $newYearReportId;
        return $this;
    }
}
