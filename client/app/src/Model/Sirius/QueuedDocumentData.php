<?php

declare(strict_types=1);

namespace App\Model\Sirius;

use App\Entity\Report\ReportSubmission;
use DateTime;

class QueuedDocumentData
{
    /** @var int */
    private $documentId;
    private $reportSubmissionId;

    /** @var int|null */
    private $ndrId;
    private $documentSyncAttempts;

    /** @var bool */
    private $isReportPdf;

    /** @var string */
    private $filename;
    private $storageReference;
    private $caseNumber;

    /** @var string|null */
    private $reportType;
    private $s3Reference;
    private $reportSubmissionUuid;

    /** @var ReportSubmission[] */
    private $reportSubmissions;

    /** @var DateTime|null */
    private $reportStartDate;
    private $reportEndDate;
    private $reportSubmitDate;

    public function supportingDocumentCanBeSynced()
    {
        return !$this->isReportPdf() && $this->getReportSubmissionUuid();
    }

    public function getCaseNumber(): string
    {
        return $this->caseNumber;
    }

    /**
     * @return QueuedDocumentData
     */
    public function setCaseNumber(string $caseNumber): self
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }

    public function getDocumentId(): int
    {
        return $this->documentId;
    }

    /**
     * @return QueuedDocumentData
     */
    public function setDocumentId(int $documentId): self
    {
        $this->documentId = $documentId;

        return $this;
    }

    public function isReportPdf(): bool
    {
        return $this->isReportPdf;
    }

    /**
     * @return QueuedDocumentData
     */
    public function setIsReportPdf(bool $isReportPdf): self
    {
        $this->isReportPdf = $isReportPdf;

        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @return QueuedDocumentData
     */
    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getStorageReference(): string
    {
        return $this->storageReference;
    }

    /**
     * @return QueuedDocumentData
     */
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
     *
     * @return QueuedDocumentData
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

    /**
     * @return QueuedDocumentData
     */
    public function setNdrId(?int $ndrId): self
    {
        $this->ndrId = $ndrId;

        return $this;
    }

    public function getReportStartDate(): ?DateTime
    {
        return $this->reportStartDate;
    }

    /**
     * @return QueuedDocumentData
     */
    public function setReportStartDate(?DateTime $reportStartDate): self
    {
        $this->reportStartDate = $reportStartDate;

        return $this;
    }

    public function getReportEndDate(): ?DateTime
    {
        return $this->reportEndDate;
    }

    /**
     * @return QueuedDocumentData
     */
    public function setReportEndDate(?DateTime $reportEndDate): self
    {
        $this->reportEndDate = $reportEndDate;

        return $this;
    }

    public function getReportSubmitDate(): ?DateTime
    {
        return $this->reportSubmitDate;
    }

    /**
     * @return QueuedDocumentData
     */
    public function setReportSubmitDate(?DateTime $reportSubmitDate): self
    {
        $this->reportSubmitDate = $reportSubmitDate;

        return $this;
    }

    public function getReportType(): ?string
    {
        return $this->reportType;
    }

    /**
     * @return QueuedDocumentData
     */
    public function setReportType(?string $reportType): self
    {
        $this->reportType = $reportType;

        return $this;
    }

    public function getReportSubmissionId(): int
    {
        return $this->reportSubmissionId;
    }

    /**
     * @return QueuedDocumentData
     */
    public function setReportSubmissionId(int $reportSubmissionId): self
    {
        $this->reportSubmissionId = $reportSubmissionId;

        return $this;
    }

    public function getReportSubmissionUuid(): ?string
    {
        return $this->reportSubmissionUuid;
    }

    /**
     * @return QueuedDocumentData
     */
    public function setReportSubmissionUuid(?string $reportSubmissionUuid): self
    {
        $this->reportSubmissionUuid = $reportSubmissionUuid;

        return $this;
    }

    public function getDocumentSyncAttempts(): ?int
    {
        return $this->documentSyncAttempts;
    }

    /**
     * @return QueuedDocumentData
     */
    public function setDocumentSyncAttempts(?int $documentSyncAttempts): self
    {
        $this->documentSyncAttempts = $documentSyncAttempts;

        return $this;
    }
}
