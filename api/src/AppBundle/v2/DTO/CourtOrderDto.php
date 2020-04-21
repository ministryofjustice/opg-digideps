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

    /**
     * @return string
     */
    public function getCaseNumber(): string
    {
        return $this->caseNumber;
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
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
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
     * @return string
     */
    public function getSupervisionLevel(): ?string
    {
        return $this->supervisionLevel;
    }

    /**
     * @param string $supervisionLevel
     * @return CourtOrderDto
     */
    public function setSupervisionLevel(?string $supervisionLevel): CourtOrderDto
    {
        $this->supervisionLevel = $supervisionLevel;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getOrderDate(): \DateTime
    {
        return $this->orderDate;
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
}
