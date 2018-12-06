<?php

namespace AppBundle\Dto;

use AppBundle\Validator\Constraints as AppAssert;

/**
 * @AppAssert\StartAndEndDateComparison
 */
class ReportSubmissionDownloadFilterDto
{
    /** @var \DateTime */
    private $fromDate;

    /** @var \DateTime */
    private $toDate;

    /** @var string */
    private $orderBy = 'id';

    /** @var string */
    private $sortOrder = 'DESC';

    /**
     * @return \DateTime
     */
    public function getFromDate()
    {
        return $this->fromDate;
    }

    /**
     * @return \DateTime
     */
    public function getToDate()
    {
        return $this->toDate;
    }

    /**
     * @param \DateTime $fromDate
     * @return ReportSubmissionDownloadFilterDto
     */
    public function setFromDate($fromDate)
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    /**
     * @param \DateTime $toDate
     * @return ReportSubmissionDownloadFilterDto
     */
    public function setToDate($toDate)
    {
        $this->toDate = $toDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @param mixed $orderBy
     * @return ReportSubmissionDownloadFilterDto
     */
    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * @param mixed $sortOrder
     * @return ReportSubmissionDownloadFilterDto
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}
