<?php declare(strict_types=1);

namespace App\Model\Sirius;

use App\Entity\Report\ReportSubmission;

class QueuedChecklistData
{
    /** @var int */
    private $checklistId;

    /** @var string */
    private $checklistUuid;
    private $caseNumber;
    private $checklistFileContents;
    private $submitterEmail;
    private $reportType;

    /** @var \DateTime */
    private $reportStartDate;
    private $reportEndDate;

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
     * @return string|null
     */
    public function getChecklistUuid(): ?string
    {
        return $this->checklistUuid;
    }

    /**
     * @param string|null $checklistUuid
     * @return QueuedChecklistData
     */
    public function setChecklistUuid(?string $checklistUuid): QueuedChecklistData
    {
        $this->checklistUuid = $checklistUuid;
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
        if (null === $this->getReportSubmissions()) {
            return null;
        }

        foreach ($this->getReportSubmissions() as $submission) {
            if ($submission->getUuid()) {
                return $submission;
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function getSubmitterEmail(): string
    {
        return $this->submitterEmail;
    }

    /**
     * @param string $submitterEmail
     * @return QueuedChecklistData
     */
    public function setSubmitterEmail(string $submitterEmail): self
    {
        $this->submitterEmail = $submitterEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getReportType(): string
    {
        return $this->reportType;
    }

    /**
     * @param string $reportType
     * @return QueuedChecklistData
     */
    public function setReportType(string $reportType): self
    {
        $this->reportType = $reportType;

        return $this;
    }
}
