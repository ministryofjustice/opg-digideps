<?php declare(strict_types=1);


namespace App\Event;

use App\Entity\Ndr\Ndr;
use App\Entity\Report\Report;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class NdrSubmittedEvent extends Event
{
    public const NAME = 'ndr.submitted';

    /** @var User */
    private $submittedBy;

    /** @var Ndr */
    private $submittedNdr;

    /** @var Report */
    private $newReport;

    public function __construct(User $submittedBy, Ndr $submittedNdr, Report $newReport)
    {
        $this->submittedBy = $submittedBy;
        $this->submittedNdr = $submittedNdr;
        $this->newReport = $newReport;
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
     * @return NdrSubmittedEvent
     */
    public function setSubmittedBy(User $submittedBy): NdrSubmittedEvent
    {
        $this->submittedBy = $submittedBy;
        return $this;
    }

    /**
     * @return Ndr
     */
    public function getSubmittedNdr(): Ndr
    {
        return $this->submittedNdr;
    }

    /**
     * @param Ndr $submittedNdr
     * @return NdrSubmittedEvent
     */
    public function setSubmittedNdr(Ndr $submittedNdr): NdrSubmittedEvent
    {
        $this->submittedNdr = $submittedNdr;
        return $this;
    }

    /**
     * @return Report
     */
    public function getNewReport(): Report
    {
        return $this->newReport;
    }

    /**
     * @param Report $newReport
     * @return NdrSubmittedEvent
     */
    public function setNewReport(Report $newReport): NdrSubmittedEvent
    {
        $this->newReport = $newReport;
        return $this;
    }
}
