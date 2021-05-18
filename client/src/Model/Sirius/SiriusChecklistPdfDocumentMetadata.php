<?php

declare(strict_types=1);

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

    public function getSubmissionId(): ?int
    {
        return $this->submissionId;
    }

    /**
     * @return SiriusChecklistPdfDocumentMetadata
     */
    public function setSubmissionId(?int $submissionId): self
    {
        $this->submissionId = $submissionId;

        return $this;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * @return SiriusChecklistPdfDocumentMetadata
     */
    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getSubmitterEmail(): string
    {
        return $this->submitterEmail;
    }

    /**
     * @return SiriusChecklistPdfDocumentMetadata
     */
    public function setSubmitterEmail(string $submitterEmail): self
    {
        $this->submitterEmail = $submitterEmail;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return SiriusChecklistPdfDocumentMetadata
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getReportingPeriodFrom(): DateTime
    {
        return $this->reportingPeriodFrom;
    }

    /**
     * @return SiriusChecklistPdfDocumentMetadata
     */
    public function setReportingPeriodFrom(DateTime $reportingPeriodFrom): self
    {
        $this->reportingPeriodFrom = $reportingPeriodFrom;

        return $this;
    }

    public function getReportingPeriodTo(): DateTime
    {
        return $this->reportingPeriodTo;
    }

    /**
     * @return SiriusChecklistPdfDocumentMetadata
     */
    public function setReportingPeriodTo(DateTime $reportingPeriodTo): self
    {
        $this->reportingPeriodTo = $reportingPeriodTo;

        return $this;
    }
}
