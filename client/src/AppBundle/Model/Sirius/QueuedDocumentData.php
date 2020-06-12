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

//        foreach ($this->getReportSubmissions() as $submission) {
//            if ($submission->getUuid()) {
//                foreach ($submission->getDocuments() as $document) {
//                    if ($this->getDocumentId() === $document->getId()) {
//                        return $submission;
//                    }
//                }
//            }
//        }
//
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
     * @return QueuedDocumentData
     */
    public function setCaseNumber(string $caseNumber): self
    {
        $this->caseNumber = $caseNumber;

        return $this;
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
     * @return QueuedDocumentData
     */
    public function setDocumentId(int $documentId): self
    {
        $this->documentId = $documentId;

        return $this;
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
     * @return QueuedDocumentData
     */
    public function setIsReportPdf(bool $isReportPdf): self
    {
        $this->isReportPdf = $isReportPdf;

        return $this;
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
     * @return QueuedDocumentData
     */
    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
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
     * @return QueuedDocumentData
     */
    public function setReportSubmissions(array $reportSubmissions): self
    {
        $this->reportSubmissions = $reportSubmissions;

        return $this;
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
     * @return QueuedDocumentData
     */
    public function setNdrId(?int $ndrId): self
    {
        $this->ndrId = $ndrId;

        return $this;
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
     * @return QueuedDocumentData
     */
    public function setReportStartDate(?DateTime $reportStartDate): self
    {
        $this->reportStartDate = $reportStartDate;

        return $this;
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
     * @return QueuedDocumentData
     */
    public function setReportEndDate(?DateTime $reportEndDate): self
    {
        $this->reportEndDate = $reportEndDate;

        return $this;
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
     * @return QueuedDocumentData
     */
    public function setReportSubmitDate(?DateTime $reportSubmitDate): self
    {
        $this->reportSubmitDate = $reportSubmitDate;

        return $this;
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
     * @return QueuedDocumentData
     */
    public function setReportType(?string $reportType): self
    {
        $this->reportType = $reportType;

        return $this;
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
     * @return QueuedDocumentData
     */
    public function setReportSubmissionId(int $reportSubmissionId): self
    {
        $this->reportSubmissionId = $reportSubmissionId;

        return $this;
    }
}
