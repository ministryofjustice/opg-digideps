<?php

declare(strict_types=1);

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

    public function getChecklistId(): int
    {
        return $this->checklistId;
    }

    public function setChecklistId(int $checklistId): QueuedChecklistData
    {
        $this->checklistId = $checklistId;

        return $this;
    }

    public function getChecklistUuid(): ?string
    {
        return $this->checklistUuid;
    }

    public function setChecklistUuid(?string $checklistUuid): QueuedChecklistData
    {
        $this->checklistUuid = $checklistUuid;

        return $this;
    }

    public function getCaseNumber(): string
    {
        return $this->caseNumber;
    }

    public function setCaseNumber(string $caseNumber): QueuedChecklistData
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }

    public function getReportStartDate(): \DateTime
    {
        return $this->reportStartDate;
    }

    public function setReportStartDate(\DateTime $reportStartDate): QueuedChecklistData
    {
        $this->reportStartDate = $reportStartDate;

        return $this;
    }

    public function getReportEndDate(): \DateTime
    {
        return $this->reportEndDate;
    }

    public function setReportEndDate(\DateTime $reportEndDate): QueuedChecklistData
    {
        $this->reportEndDate = $reportEndDate;

        return $this;
    }

    public function getChecklistFileContents(): string
    {
        return $this->checklistFileContents;
    }

    public function setChecklistFileContents(string $checklistFileContents): QueuedChecklistData
    {
        $this->checklistFileContents = $checklistFileContents;

        return $this;
    }

    public function getReportSubmissions(): ?array
    {
        return $this->reportSubmissions;
    }

    public function setReportSubmissions(?array $reportSubmissions): QueuedChecklistData
    {
        $this->reportSubmissions = $reportSubmissions;

        return $this;
    }

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

    public function getSubmitterEmail(): string
    {
        return $this->submitterEmail;
    }

    /**
     * @return QueuedChecklistData
     */
    public function setSubmitterEmail(string $submitterEmail): self
    {
        $this->submitterEmail = $submitterEmail;

        return $this;
    }

    public function getReportType(): string
    {
        return $this->reportType;
    }

    /**
     * @return QueuedChecklistData
     */
    public function setReportType(string $reportType): self
    {
        $this->reportType = $reportType;

        return $this;
    }
}
