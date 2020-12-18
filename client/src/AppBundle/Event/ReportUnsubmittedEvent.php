<?php declare(strict_types=1);


namespace AppBundle\Event;

use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class ReportUnsubmittedEvent extends Event
{
    public const NAME = 'report.unsubmitted';

    /** @var Report */
    private $unsubmittedReport;


    /** @var User */
    private $unsubmittedBy;

    /** @var string|int|null */
    private $newYearReportId;

    /**
     * @var string
     */
    private string $trigger;

    public function __construct(Report $unsubmittedReport, User $unsubmittedBy, $newYearReportId, string $trigger)
    {
        $this->unsubmittedReport = $unsubmittedReport;
        $this->submittedBy = $unsubmittedBy;
        $this->newYearReportId = $newYearReportId;
        $this->trigger = $trigger;
    }

    public function getTrigger()
    {
        return $this->trigger;
    }

    /**
     * @return Report
     */
    public function getUnsubmittedReport(): Report
    {
        return $this->unsubmittedReport;
    }

    /**
     * @param Report $unsubmittedReport
     * @return ReportUnsubmittedEvent
     */
    public function setUnsubmittedReport(Report $unsubmittedReport): ReportUnsubmittedEvent
    {
        $this->unsubmittedReport = $unsubmittedReport;
        return $this;
    }

    /**
     * @return User
     */
    public function getUnsubmittedBy(): User
    {
        return $this->unsubmittedBy;
    }

    /**
     * @param User $unsubmittedBy
     * @return ReportUnsubmittedEvent
     */
    public function setUnsubmittedBy(User $unsubmittedBy): ReportUnsubmittedEvent
    {
        $this->unsubmittedBy = $unsubmittedBy;
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
     * @return ReportUnsubmittedEvent
     */
    public function setNewYearReportId($newYearReportId): ReportUnsubmittedEvent
    {
        $this->newYearReportId = $newYearReportId;
        return $this;
    }
}
