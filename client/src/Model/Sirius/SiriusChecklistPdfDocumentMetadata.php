<?php declare(strict_types=1);

namespace App\Model\Sirius;

use DateTime;

class SiriusChecklistPdfDocumentMetadata implements SiriusMetadataInterface
{
    /** @var int */
    private $year;

    /** @var int|null */
    private $submissionId;

    /** @var string */
    private $submitterEmail;
    private $type;

    /** @var DateTime */
    private $reportingPeriodFrom;
    private $reportingPeriodTo;

    /**
     * @return int|null
     */
    public function getSubmissionId(): ?int
    {
        return $this->submissionId;
    }

    /**
     * @param int|null $submissionId
     * @return SiriusChecklistPdfDocumentMetadata
     */
    public function setSubmissionId(?int $submissionId): self
    {
        $this->submissionId = $submissionId;
        return $this;
    }

    /**
     * @return int
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * @param int $year
     * @return SiriusChecklistPdfDocumentMetadata
     */
    public function setYear(int $year): self
    {
        $this->year = $year;
        return $this;
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
     * @return SiriusChecklistPdfDocumentMetadata
     */
    public function setSubmitterEmail(string $submitterEmail): self
    {
        $this->submitterEmail = $submitterEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return SiriusChecklistPdfDocumentMetadata
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getReportingPeriodFrom(): DateTime
    {
        return $this->reportingPeriodFrom;
    }

    /**
     * @param DateTime $reportingPeriodFrom
     * @return SiriusChecklistPdfDocumentMetadata
     */
    public function setReportingPeriodFrom(DateTime $reportingPeriodFrom): self
    {
        $this->reportingPeriodFrom = $reportingPeriodFrom;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getReportingPeriodTo(): DateTime
    {
        return $this->reportingPeriodTo;
    }

    /**
     * @param DateTime $reportingPeriodTo
     * @return SiriusChecklistPdfDocumentMetadata
     */
    public function setReportingPeriodTo(DateTime $reportingPeriodTo): self
    {
        $this->reportingPeriodTo = $reportingPeriodTo;
        return $this;
    }
}
