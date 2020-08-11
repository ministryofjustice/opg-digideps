<?php

namespace AppBundle\Mapper\ReportSatisfaction;

use AppBundle\Validator\Constraints as AppAssert;
use AppBundle\Validator\Constraints\StartEndDateComparableInterface;

/**
 * @AppAssert\EndDateNotBeforeStartDate
 */
class ReportSatisfactionSummaryQuery implements StartEndDateComparableInterface
{
    /** @var \DateTime */
    private $startDate;

    /** @var \DateTime */
    private $endDate;

    /** @var string */
    private $orderBy = 'id';

    /** @var string */
    private $sortOrder = 'DESC';

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
     * @param \DateTime $startDate
     * @return ReportSatisfactionSummaryQuery
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @param \DateTime $endDate
     * @return ReportSatisfactionSummaryQuery
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

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
     * @return ReportSatisfactionSummaryQuery
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
     * @return ReportSatisfactionSummaryQuery
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}
