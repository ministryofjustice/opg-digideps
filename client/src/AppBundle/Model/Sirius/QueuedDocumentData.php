<?php declare(strict_types=1);


namespace AppBundle\Model\Sirius;

use AppBundle\Entity\Report\ReportSubmission;
use DateTime;

class QueuedDocumentData
{
    /** @var string */
    private $caseNumber;

    /** @var int */
    private $documentId;

    /** @var bool */
    private $isReportPdf;

    /** @var string */
    private $filename;

    /** @var string */
    private $storageReference;

    /** @var ReportSubmission[] */
    private $reportSubmissions;

    /** @var int|null */
    private $ndrId;

    /** @var DateTime|null */
    private $reportStartDate;

    /** @var DateTime|null */
    private $reportEndDate;

    /** @var DateTime|null */
    private $reportSubmitDate;

    /** @var string|null */
    private $reportType;

    /** @var int */
    private $reportSubmissionId;

    public function getSyncedReportSubmission(): ?ReportSubmission
    {
        foreach ($this->getReportSubmissions() as $submission) {
            if ($submission->getUuid()) {
                return $submission;
            }
        }

        return null;
    }

    public function supportingDocumentCanBeSynced()
    {
        return !$this->isReportPdf() && $this->getSyncedReportSubmission();
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
     */
    public function setCaseNumber(string $caseNumber): void
    {
        $this->caseNumber = $caseNumber;
    }

    /**
     * @return int
     */
    public function getDocumentId(): int
    {
        return $this->documentId;
    }

    /**
     * @param int $documentId
     */
    public function setDocumentId(int $documentId): void
    {
        $this->documentId = $documentId;
    }

    /**
     * @return bool
     */
    public function isReportPdf(): bool
    {
        return $this->isReportPdf;
    }

    /**
     * @param bool $isReportPdf
     */
    public function setIsReportPdf(bool $isReportPdf): void
    {
        $this->isReportPdf = $isReportPdf;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getStorageReference(): string
    {
        return $this->storageReference;
    }

    /**
     * @param string $storageReference
     */
    public function setStorageReference(string $storageReference): void
    {
        $this->storageReference = $storageReference;
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
    public function setReportSubmissions(array $reportSubmissions): void
    {
        $this->reportSubmissions = $reportSubmissions;
    }

    /**
     * @return int|null
     */
    public function getNdrId(): ?int
    {
        return $this->ndrId;
    }

    /**
     * @param int|null $ndrId
     */
    public function setNdrId(?int $ndrId): void
    {
        $this->ndrId = $ndrId;
    }

    /**
     * @return DateTime|null
     */
    public function getReportStartDate(): ?DateTime
    {
        return $this->reportStartDate;
    }

    /**
     * @param DateTime|null $reportStartDate
     */
    public function setReportStartDate(?DateTime $reportStartDate): void
    {
        $this->reportStartDate = $reportStartDate;
    }

    /**
     * @return DateTime|null
     */
    public function getReportEndDate(): ?DateTime
    {
        return $this->reportEndDate;
    }

    /**
     * @param DateTime|null $reportEndDate
     */
    public function setReportEndDate(?DateTime $reportEndDate): void
    {
        $this->reportEndDate = $reportEndDate;
    }

    /**
     * @return DateTime|null
     */
    public function getReportSubmitDate(): ?DateTime
    {
        return $this->reportSubmitDate;
    }

    /**
     * @param DateTime|null $reportSubmitDate
     */
    public function setReportSubmitDate(?DateTime $reportSubmitDate): void
    {
        $this->reportSubmitDate = $reportSubmitDate;
    }

    /**
     * @return string|null
     */
    public function getReportType(): ?string
    {
        return $this->reportType;
    }

    /**
     * @param string|null $reportType
     */
    public function setReportType(?string $reportType): void
    {
        $this->reportType = $reportType;
    }

    /**
     * @return int
     */
    public function getReportSubmissionId(): int
    {
        return $this->reportSubmissionId;
    }

    /**
     * @param int $reportSubmissionId
     */
    public function setReportSubmissionId(int $reportSubmissionId): void
    {
        $this->reportSubmissionId = $reportSubmissionId;
    }
}
