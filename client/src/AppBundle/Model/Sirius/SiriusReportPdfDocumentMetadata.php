<?php declare(strict_types=1);


namespace AppBundle\Model\Sirius;

use DateTime;
use JMS\Serializer\Annotation\Type;

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

    /**
     * @return DateTime
     */
    public function getReportingPeriodFrom(): DateTime
    {
        return $this->reportingPeriodFrom;
    }

    /**
     * @param DateTime $reportingPeriodFrom
     * @return SiriusReportPdfDocumentMetadata
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
     * @return SiriusReportPdfDocumentMetadata
     */
    public function setReportingPeriodTo(DateTime $reportingPeriodTo): self
    {
        $this->reportingPeriodTo = $reportingPeriodTo;

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
     * @return SiriusReportPdfDocumentMetadata
     */
    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateSubmitted(): DateTime
    {
        return $this->dateSubmitted;
    }

    /**
     * @param DateTime $dateSubmitted
     * @return SiriusReportPdfDocumentMetadata
     */
    public function setDateSubmitted(DateTime $dateSubmitted): self
    {
        $this->dateSubmitted = $dateSubmitted;

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
     * @return SiriusReportPdfDocumentMetadata
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSubmissionId(): ?int
    {
        return $this->submissionId;
    }

    /**
     * @param int|null $submissionId
     * @return SiriusReportPdfDocumentMetadata
     */
    public function setSubmissionId(?int $submissionId): self
    {
        $this->submissionId = $submissionId;

        return $this;
    }
}
