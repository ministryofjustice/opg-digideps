<?php declare(strict_types=1);

namespace App\Mapper;

use App\Validator\Constraints as AppAssert;
use App\Validator\Constraints\StartEndDateComparableInterface;
use DateTime;

/**
 * @AppAssert\EndDateNotBeforeStartDate
 */
class DateRangeQuery implements StartEndDateComparableInterface
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
     * @return DateRangeQuery
     */
    public function setStartDate(?DateTime $startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @param DateTime|null $endDate
     * @return DateRangeQuery
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
     * @return DateRangeQuery
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
     * @return DateRangeQuery
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}
