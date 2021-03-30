<?php declare(strict_types=1);


namespace App\Event;

use App\Entity\Report\Report;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class ReportUnsubmittedEvent extends Event
{
    public const NAME = 'report.unsubmitted';

    /** @var Report */
    private $unsubmittedReport;


    /** @var User */
    private $unsubmittedBy;

    /**
     * @var string
     */
    private string $trigger;

    public function __construct(Report $unsubmittedReport, User $unsubmittedBy, string $trigger)
    {
        $this->unsubmittedReport = $unsubmittedReport;
        $this->unsubmittedBy = $unsubmittedBy;
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
}
