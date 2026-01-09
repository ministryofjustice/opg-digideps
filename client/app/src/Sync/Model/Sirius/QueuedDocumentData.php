<?php

declare(strict_types=1);

namespace App\Sync\Model\Sirius;

use App\Entity\Report\ReportSubmission;

class QueuedDocumentData
{
    private int $documentId;
    private int $reportSubmissionId;
    private ?int $ndrId = null;
    private ?int $documentSyncAttempts = null;
    private bool $isReportPdf;
    private string $filename;
    private string $storageReference;
    private string $caseNumber;

    private ?string $reportType = null;
    private ?string $reportSubmissionUuid = null;

    /** @var ReportSubmission[] */
    private array $reportSubmissions;

    private ?\DateTime $reportStartDate = null;
    private ?\DateTime $reportEndDate = null;
    private ?\DateTime $reportSubmitDate = null;

    public function supportingDocumentCanBeSynced(): bool
    {
        return !$this->isReportPdf() && $this->getReportSubmissionUuid();
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

    public function getDocumentId(): int
    {
        return $this->documentId;
    }

    public function setDocumentId(int $documentId): self
    {
        $this->documentId = $documentId;

        return $this;
    }

    public function isReportPdf(): bool
    {
        return $this->isReportPdf;
    }

    public function setIsReportPdf(bool $isReportPdf): self
    {
        $this->isReportPdf = $isReportPdf;

        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getStorageReference(): string
    {
        return $this->storageReference;
    }

    public function setStorageReference(string $storageReference): self
    {
        $this->storageReference = $storageReference;

        return $this;
    }

    /**
     * @return ReportSubmission[]
     */
    public function getReportSubmissions(): array
    {
        return $this->reportSubmissions;
    }

    /**
     * @param ReportSubmission[] $reportSubmissions
     */
    public function setReportSubmissions(array $reportSubmissions): self
    {
        $this->reportSubmissions = $reportSubmissions;

        return $this;
    }

    public function getNdrId(): ?int
    {
        return $this->ndrId;
    }

    public function setNdrId(?int $ndrId): self
    {
        $this->ndrId = $ndrId;

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

    public function getReportSubmitDate(): ?\DateTime
    {
        return $this->reportSubmitDate;
    }

    public function setReportSubmitDate(?\DateTime $reportSubmitDate): self
    {
        $this->reportSubmitDate = $reportSubmitDate;

        return $this;
    }

    public function getReportType(): ?string
    {
        return $this->reportType;
    }

    public function setReportType(?string $reportType): self
    {
        $this->reportType = $reportType;

        return $this;
    }

    public function getReportSubmissionId(): int
    {
        return $this->reportSubmissionId;
    }

    public function setReportSubmissionId(int $reportSubmissionId): self
    {
        $this->reportSubmissionId = $reportSubmissionId;

        return $this;
    }

    public function getReportSubmissionUuid(): ?string
    {
        return $this->reportSubmissionUuid;
    }

    public function setReportSubmissionUuid(?string $reportSubmissionUuid): self
    {
        $this->reportSubmissionUuid = $reportSubmissionUuid;

        return $this;
    }

    public function getDocumentSyncAttempts(): ?int
    {
        return $this->documentSyncAttempts;
    }

    public function setDocumentSyncAttempts(?int $documentSyncAttempts): self
    {
        $this->documentSyncAttempts = $documentSyncAttempts;

        return $this;
    }
}
