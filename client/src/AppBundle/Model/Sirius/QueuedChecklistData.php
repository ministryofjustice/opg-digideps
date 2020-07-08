<?php declare(strict_types=1);

namespace AppBundle\Model\Sirius;

use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;

class QueuedChecklistData
{
    /** @var int */
    private $checklistId;

    /** @var string */
    private $caseNumber;

    /** @var \DateTime */
    private $reportStartDate;

    /** @var \DateTime */
    private $reportEndDate;

    /** @var string */
    private $checklistFileContents;

    /** @var array|null */
    private $reportSubmissions;

    /**
     * @return int
     */
    public function getChecklistId(): int
    {
        return $this->checklistId;
    }

    /**
     * @param int $checklistId
     * @return QueuedChecklistData
     */
    public function setChecklistId(int $checklistId): QueuedChecklistData
    {
        $this->checklistId = $checklistId;
        return $this;
    }

    /**
     * @return string
     */
    public function getCaseNumber(): string
    {
        return $this->caseNumber;
    }

    /**
     * @param string $caseNumber
     * @return QueuedChecklistData
     */
    public function setCaseNumber(string $caseNumber): QueuedChecklistData
    {
        $this->caseNumber = $caseNumber;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getReportStartDate(): \DateTime
    {
        return $this->reportStartDate;
    }

    /**
     * @param \DateTime $reportStartDate
     * @return QueuedChecklistData
     */
    public function setReportStartDate(\DateTime $reportStartDate): QueuedChecklistData
    {
        $this->reportStartDate = $reportStartDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getReportEndDate(): \DateTime
    {
        return $this->reportEndDate;
    }

    /**
     * @param \DateTime $reportEndDate
     * @return QueuedChecklistData
     */
    public function setReportEndDate(\DateTime $reportEndDate): QueuedChecklistData
    {
        $this->reportEndDate = $reportEndDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getChecklistFileContents(): string
    {
        return $this->checklistFileContents;
    }

    /**
     * @param string $checklistFileContents
     * @return QueuedChecklistData
     */
    public function setChecklistFileContents(string $checklistFileContents): QueuedChecklistData
    {
        $this->checklistFileContents = $checklistFileContents;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getReportSubmissions(): ?array
    {
        return $this->reportSubmissions;
    }

    /**
     * @param array|null $reportSubmissions
     * @return QueuedChecklistData
     */
    public function setReportSubmissions(?array $reportSubmissions): QueuedChecklistData
    {
        $this->reportSubmissions = $reportSubmissions;
        return $this;
    }

    /**
     * @return ReportSubmission|null
     */
    public function getSyncedReportSubmission(): ?ReportSubmission
    {
        foreach ($this->getReportSubmissions() as $submission) {
            if ($submission->getUuid()) {
                return $submission;
            }
        }

        return null;
    }
}
