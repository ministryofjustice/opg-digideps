<?php

namespace AppBundle\v2\DTO;

class ReportDto
{
    /** @var int */
    private $id;

    /** @var bool */
    private $submitted;

    /** @var \DateTime */
    private $dueDate;

    /** @var \DateTime */
    private $submitDate;

    /** @var \DateTime */
    private $unSubmitDate;

    /** @var \DateTime */
    private $startDate;

    /** @var \DateTime */
    private $endDate;

    /** @var array */
    private $availableSections;

    /** @var StatusDto */
    private $status;

    /** @var string */
    private $type;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function getSubmitted()
    {
        return $this->submitted;
    }

    /**
     * @return \DateTime
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * @return \DateTime
     */
    public function getSubmitDate()
    {
        return $this->submitDate;
    }

    /**
     * @return \DateTime
     */
    public function getUnSubmitDate()
    {
        return $this->unSubmitDate;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @return array
     */
    public function getAvailableSections()
    {
        return $this->availableSections;
    }

    /**
     * @return StatusDto
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $id
     * @return ReportDto
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param bool $submitted
     * @return ReportDto
     */
    public function setSubmitted($submitted)
    {
        $this->submitted = $submitted;
        return $this;
    }

    /**
     * @param \DateTime $dueDate
     * @return ReportDto
     */
    public function setDueDate(\DateTime $dueDate)
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    /**
     * @param \DateTime $submitDate
     * @return ReportDto
     */
    public function setSubmitDate(\DateTime $submitDate)
    {
        $this->submitDate = $submitDate;
        return $this;
    }

    /**
     * @param \DateTime $unSubmitDate
     * @return ReportDto
     */
    public function setUnSubmitDate(\DateTime $unSubmitDate)
    {
        $this->unSubmitDate = $unSubmitDate;
        return $this;
    }

    /**
     * @param \DateTime $startDate
     * @return ReportDto
     */
    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @param \DateTime $endDate
     * @return ReportDto
     */
    public function setEndDate(\DateTime $endDate)
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @param array $availableSections
     * @return ReportDto
     */
    public function setAvailableSections($availableSections)
    {
        $this->availableSections = $availableSections;
        return $this;
    }

    /**
     * @param StatusDto $status
     * @return ReportDto
     */
    public function setStatus(StatusDto $status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param string $type
     * @return ReportDto
     */
    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }
}
