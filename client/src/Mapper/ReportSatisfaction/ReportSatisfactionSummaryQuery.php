<?php declare(strict_types=1);

namespace App\Mapper\ReportSatisfaction;

use App\Validator\Constraints as AppAssert;
use App\Validator\Constraints\StartEndDateComparableInterface;
use DateTime;

/**
 * @AppAssert\EndDateNotBeforeStartDate
 */
class ReportSatisfactionSummaryQuery implements StartEndDateComparableInterface
{
    /** @var DateTime|null */
    private $startDate;

    /** @var DateTime|null */
    private $endDate;

    /** @var string */
    private $orderBy = 'id';

    /** @var string */
    private $sortOrder = 'DESC';

    /**
     * @return DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @return DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param DateTime|null $startDate
     * @return ReportSatisfactionSummaryQuery
     */
    public function setStartDate(?DateTime $startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @param DateTime|null $endDate
     * @return ReportSatisfactionSummaryQuery
     */
    public function setEndDate(?DateTime $endDate)
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
