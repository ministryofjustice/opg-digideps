<?php

namespace AppBundle\Mapper\ReportSubmission;

use AppBundle\Validator\Constraints as AppAssert;

/**
 * @AppAssert\StartAndEndDateComparison
 */
class ReportSubmissionSummaryQuery
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
     * @return ReportSubmissionSummaryQuery
     */
    public function setFromDate($fromDate)
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    /**
     * @param \DateTime $toDate
     * @return ReportSubmissionSummaryQuery
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
     * @return ReportSubmissionSummaryQuery
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
     * @return ReportSubmissionSummaryQuery
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}
