<?php

declare(strict_types=1);

namespace App\Model\Sirius;

use DateTime;

class SiriusReportPdfDocumentMetadata implements SiriusMetadataInterface
{
    /** @var DateTime */
    private $reportingPeriodFrom;
    private $reportingPeriodTo;
    private $dateSubmitted;

    /** @var int */
    private $year;

    /** @var string */
    private $type;

    /** @var int|null */
    private $submissionId;

    public function getReportingPeriodFrom(): DateTime
    {
        return $this->reportingPeriodFrom;
    }

    public function setReportingPeriodFrom(DateTime $reportingPeriodFrom): self
    {
        $this->reportingPeriodFrom = $reportingPeriodFrom;

        return $this;
    }

    public function getReportingPeriodTo(): DateTime
    {
        return $this->reportingPeriodTo;
    }

    public function setReportingPeriodTo(DateTime $reportingPeriodTo): self
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

    public function getDateSubmitted(): DateTime
    {
        return $this->dateSubmitted;
    }

    public function setDateSubmitted(DateTime $dateSubmitted): self
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
