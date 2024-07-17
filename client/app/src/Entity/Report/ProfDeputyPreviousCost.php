<?php

namespace App\Entity\Report;

use App\Validator\Constraints as AppAssert;
use App\Validator\Constraints\StartEndDateComparableInterface;
use DateTime;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AppAssert\EndDateNotBeforeStartDate(groups={"prof-deputy-prev-costs"})
 *
 * @AppAssert\EndDateNotGreaterThanFifteenMonths(groups={"prof-deputy-prev-costs"})
 */
class ProfDeputyPreviousCost implements StartEndDateComparableInterface
{
    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @JMS\Groups({"profDeputyPrevCosts"})
     *
     * @Assert\NotBlank(message="profDeputyPreviousCost.startDate.notBlank", groups={"prof-deputy-prev-costs"})
     *
     * @Assert\Type(type="DateTimeInterface",message="profDeputyPreviousCost.startDate.notValid", groups={"prof-deputy-prev-costs"})
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @JMS\Groups({"profDeputyPrevCosts"})
     *
     * @Assert\NotBlank(message="profDeputyPreviousCost.endDate.notBlank", groups={"prof-deputy-prev-costs"})
     *
     * @Assert\Type(type="DateTimeInterface",message="profDeputyPreviousCost.endDate.notValid", groups={"prof-deputy-prev-costs"})
     */
    private $endDate;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"profDeputyPrevCosts"})
     *
     * @Assert\NotBlank(message="profDeputyPreviousCost.amount.notBlank", groups={"prof-deputy-prev-costs"})
     *
     * @Assert\Range(min=0.01, max=10000000, notInRangeMessage="profDeputyPreviousCost.amount.notInRangeMessage", groups={"prof-deputy-prev-costs"})
     */
    private $amount;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     *
     * @return ProfDeputyPreviousCost
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     *
     * @return ProfDeputyPreviousCost
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param string $amount
     *
     * @return ProfDeputyPreviousCost
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }
}
