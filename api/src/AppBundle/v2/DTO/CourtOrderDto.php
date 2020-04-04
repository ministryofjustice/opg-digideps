<?php

namespace AppBundle\v2\DTO;

class CourtOrderDto
{
    /** @var string */
    private $caseNumber;

    /** @var string */
    private $type;

    /** @var string */
    private $supervisionLevel;

    /** @var \DateTime */
    private $orderDate;

    /** @var ClientDto */
    private $client;

    /** @var array */
    private $reports;

    /** @return string */
    public function getCaseNumber(): string
    {
        return $this->caseNumber;
    }

    /** @return string */
    public function getType(): string
    {
        return $this->type;
    }

    /** @return string */
    public function getSupervisionLevel(): string
    {
        return $this->supervisionLevel;
    }

    /** @return \DateTime */
    public function getOrderDate(): \DateTime
    {
        return $this->orderDate;
    }

    /** @return ClientDto */
    public function getClient(): ClientDto
    {
        return $this->client;
    }

    /** @return array */
    public function getReports(): array
    {
        return $this->reports;
    }

    /**
     * @param string $caseNumber
     * @return CourtOrderDto
     */
    public function setCaseNumber(string $caseNumber): CourtOrderDto
    {
        $this->caseNumber = $caseNumber;
        return $this;
    }

    /**
     * @param string $type
     * @return CourtOrderDto
     */
    public function setType(string $type): CourtOrderDto
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param string $supervisionLevel
     * @return CourtOrderDto
     */
    public function setSupervisionLevel(string $supervisionLevel): CourtOrderDto
    {
        $this->supervisionLevel = $supervisionLevel;
        return $this;
    }

    /**
     * @param \DateTime $orderDate
     * @return CourtOrderDto
     */
    public function setOrderDate(\DateTime $orderDate): CourtOrderDto
    {
        $this->orderDate = $orderDate;
        return $this;
    }

    /**
     * @param ClientDto $client
     * @return CourtOrderDto
     */
    public function setClient(ClientDto $client): CourtOrderDto
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @param array $reports
     * @return CourtOrderDto
     */
    public function setReports(array $reports): CourtOrderDto
    {
        $this->reports = $reports;
        return $this;
    }
}
