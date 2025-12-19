<?php

declare(strict_types=1);

namespace App\Sync\Model\Sirius;

/**
 * This class is only serialized, which is why it has unused getters.
 */
class SiriusReportPdfDocumentMetadata implements SiriusMetadataInterface
{
    private ?\DateTime $reportingPeriodFrom;
    private ?\DateTime $reportingPeriodTo;
    private ?\DateTime $dateSubmitted;
    private int $year;
    private ?int $submissionId;
    private string $type;

    public function getReportingPeriodFrom(): ?\DateTime
    {
        return $this->reportingPeriodFrom;
    }

    public function setReportingPeriodFrom(?\DateTime $reportingPeriodFrom): self
    {
        $this->reportingPeriodFrom = $reportingPeriodFrom;

        return $this;
    }

    public function getReportingPeriodTo(): ?\DateTime
    {
        return $this->reportingPeriodTo;
    }

    public function setReportingPeriodTo(?\DateTime $reportingPeriodTo): self
    {
        $this->reportingPeriodTo = $reportingPeriodTo;

        return $this;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getDateSubmitted(): ?\DateTime
    {
        return $this->dateSubmitted;
    }

    public function setDateSubmitted(?\DateTime $dateSubmitted): self
    {
        $this->dateSubmitted = $dateSubmitted;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSubmissionId(): ?int
    {
        return $this->submissionId;
    }

    public function setSubmissionId(?int $submissionId): self
    {
        $this->submissionId = $submissionId;

        return $this;
    }
}
