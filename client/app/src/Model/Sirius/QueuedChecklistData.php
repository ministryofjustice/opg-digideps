<?php

declare(strict_types=1);

namespace App\Model\Sirius;

use App\Entity\Report\ReportSubmission;

class QueuedChecklistData
{
    private int $checklistId;
    private ?string $checklistUuid = null;
    private string $caseNumber;
    private string $checklistFileContents;
    private string $submitterEmail;
    private string $reportType;
    private ?\DateTime $reportStartDate = null;
    private ?\DateTime $reportEndDate = null;
    private ?array $reportSubmissions = null;

    public function getChecklistId(): int
    {
        return $this->checklistId;
    }

    public function setChecklistId(int $checklistId): self
    {
        $this->checklistId = $checklistId;

        return $this;
    }

    public function getChecklistUuid(): ?string
    {
        return $this->checklistUuid;
    }

    public function setChecklistUuid(?string $checklistUuid): self
    {
        $this->checklistUuid = $checklistUuid;

        return $this;
    }

    public function getCaseNumber(): string
    {
        return $this->caseNumber;
    }

    public function setCaseNumber(string $caseNumber): self
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }

    public function getReportStartDate(): ?\DateTime
    {
        return $this->reportStartDate;
    }

    public function setReportStartDate(?\DateTime $reportStartDate): self
    {
        $this->reportStartDate = $reportStartDate;

        return $this;
    }

    public function getReportEndDate(): ?\DateTime
    {
        return $this->reportEndDate;
    }

    public function setReportEndDate(?\DateTime $reportEndDate): self
    {
        $this->reportEndDate = $reportEndDate;

        return $this;
    }

    public function getChecklistFileContents(): string
    {
        return $this->checklistFileContents;
    }

    public function setChecklistFileContents(string $checklistFileContents): self
    {
        $this->checklistFileContents = $checklistFileContents;

        return $this;
    }

    public function getReportSubmissions(): ?array
    {
        return $this->reportSubmissions;
    }

    public function setReportSubmissions(?array $reportSubmissions): self
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

    public function setSubmitterEmail(string $submitterEmail): self
    {
        $this->submitterEmail = $submitterEmail;

        return $this;
    }

    public function getReportType(): string
    {
        return $this->reportType;
    }

    public function setReportType(string $reportType): self
    {
        $this->reportType = $reportType;

        return $this;
    }
}
