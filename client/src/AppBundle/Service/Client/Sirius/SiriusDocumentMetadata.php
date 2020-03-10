<?php declare(strict_types=1);


namespace AppBundle\Service\Client\Sirius;


use DateTime;

class SiriusDocumentMetadata
{
    /** @var DateTime */
    private $reportingPeriodFrom;

    /** @var DateTime */
    private $reportingPeriodTo;

    /** @var string */
    private $year;

    /** @var DateTime */
    private $dateSubmitted;

    /** @var string */
    private $orderType;

    /**
     * @return DateTime
     */
    public function getReportingPeriodFrom(): DateTime
    {
        return $this->reportingPeriodFrom;
    }

    /**
     * @param DateTime $reportingPeriodFrom
     * @return SiriusDocumentMetadata
     */
    public function setReportingPeriodFrom(DateTime $reportingPeriodFrom): self
    {
        $this->reportingPeriodFrom = $reportingPeriodFrom;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderType(): string
    {
        return $this->orderType;
    }

    /**
     * @param string $orderType
     * @return SiriusDocumentMetadata
     */
    public function setOrderType(string $orderType): self
    {
        $this->orderType = $orderType;

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
     * @return SiriusDocumentMetadata
     */
    public function setReportingPeriodTo(DateTime $reportingPeriodTo): self
    {
        $this->reportingPeriodTo = $reportingPeriodTo;

        return $this;
    }

    /**
     * @return string
     */
    public function getYear(): string
    {
        return $this->year;
    }

    /**
     * @param string $year
     * @return SiriusDocumentMetadata
     */
    public function setYear(string $year): self
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
     * @return SiriusDocumentMetadata
     */
    public function setDateSubmitted(DateTime $dateSubmitted): self
    {
        $this->dateSubmitted = $dateSubmitted;

        return $this;
    }
}
