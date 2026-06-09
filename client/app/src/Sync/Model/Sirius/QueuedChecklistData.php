<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Sync\Model\Sirius;

use OPG\Digideps\Frontend\Entity\Report\ReportSubmission;

class QueuedChecklistData
{
    private ?int $checklistId;
    private ?string $checklistUuid = null;
    private string $caseNumber;
    private string $checklistFileContents;
    private ?string $submitterEmail;
    private string $reportType;
    private ?\DateTime $reportStartDate = null;
    private ?\DateTime $reportEndDate = null;
    private ?array $reportSubmissions = null;

    public function getChecklistId(): ?int
    {
        return $this->checklistId;
    }

    public function setChecklistId(?int $checklistId): static
    {
        $this->checklistId = $checklistId;

        return $this;
    }

    public function getChecklistUuid(): ?string
    {
        return $this->checklistUuid;
    }

    public function setChecklistUuid(?string $checklistUuid): static
    {
        $this->checklistUuid = $checklistUuid;

        return $this;
    }

    public function getCaseNumber(): string
    {
        return $this->caseNumber;
    }

    public function setCaseNumber(string $caseNumber): static
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }

    public function getReportStartDate(): ?\DateTime
    {
        return $this->reportStartDate;
    }

    public function setReportStartDate(?\DateTime $reportStartDate): static
    {
        $this->reportStartDate = $reportStartDate;

        return $this;
    }

    public function getReportEndDate(): ?\DateTime
    {
        return $this->reportEndDate;
    }

    public function setReportEndDate(?\DateTime $reportEndDate): static
    {
        $this->reportEndDate = $reportEndDate;

        return $this;
    }

    public function getChecklistFileContents(): string
    {
        return $this->checklistFileContents;
    }

    public function setChecklistFileContents(string $checklistFileContents): static
    {
        $this->checklistFileContents = $checklistFileContents;

        return $this;
    }

    public function getReportSubmissions(): ?array
    {
        return $this->reportSubmissions;
    }

    public function setReportSubmissions(?array $reportSubmissions): static
    {
        $this->reportSubmissions = $reportSubmissions;

        return $this;
    }

    public function getSyncedReportSubmission(): ?ReportSubmission
    {
        if ($this->getReportSubmissions() === null) {
            return null;
        }

        foreach ($this->getReportSubmissions() as $submission) {
            if ($submission->getUuid()) {
                return $submission;
            }
        }

        return null;
    }

    public function getSubmitterEmail(): ?string
    {
        return $this->submitterEmail;
    }

    public function setSubmitterEmail(?string $submitterEmail): static
    {
        $this->submitterEmail = $submitterEmail;

        return $this;
    }

    public function getReportType(): string
    {
        return $this->reportType;
    }

    public function setReportType(string $reportType): static
    {
        $this->reportType = $reportType;

        return $this;
    }
}
