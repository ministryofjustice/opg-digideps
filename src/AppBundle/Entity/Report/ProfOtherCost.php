<?php

namespace AppBundle\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class ProfOtherCost
{
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"prof-other-costs"})
     */
    private $profOtherCostTypeId;

    /**
     * @var decimal
     *
     * @JMS\Type("string")
     * @JMS\Groups({"prof-other-costs"})
     * @Assert\Type(type="numeric", message="debt.amount.notNumeric", groups={"debts"})
     * @Assert\Range(min=0, max=100000000, minMessage = "debt.amount.minMessage", maxMessage = "debt.amount.maxMessage", groups={"debts"})
     */
    private $amount;

    /**
     * @var string
     * @JMS\Groups({"prof-other-costs"})
     * @JMS\Type("boolean")
     */
    private $hasMoreDetails;

    /**
     * @var string
     * @JMS\Groups({"prof-other-costs"})
     * @JMS\Type("string")
     *
     * @Assert\NotBlank(message="profOtherCost.moreDetails.notEmpty", groups={"prof-other-cost-more-details"})
     */
    private $moreDetails;

    /**
     * Debt constructor.
     *
     * @param $profOtherCostTypeId
     * @param decimal $amount
     * @param string  $hasMoreDetails
     * @param string  $moreDetails
     */
    public function __construct($profOtherCostTypeId, $amount, $hasMoreDetails, $moreDetails)
    {
        $this->profOtherCostTypeId = $profOtherCostTypeId;
        $this->amount = $amount;
        $this->hasMoreDetails = $hasMoreDetails;
        $this->moreDetails = $moreDetails;
    }

    /**
     * @return array
     */
    public static function getProfDeputyOtherCostTypeIds()
    {
        return self::$profDeputyOtherCostTypeIds;
    }

    /**
     * @param array $debtTypeIds
     */
    public static function setProfDeputyOtherCostTypeIds($profDeputyOtherCostTypeIds)
    {
        self::$profDeputyOtherCostTypeIds = $profDeputyOtherCostTypeIds;
    }

    /**
     * @return mixed
     */
    public function getProfOtherCostTypeId()
    {
        return $this->profOtherCostTypeId;
    }

    /**
     * @param mixed $debtTypeId
     */
    public function setProfOtherCostTypeId($profOtherCostTypeId)
    {
        $this->profOtherCostTypeId = $profOtherCostTypeId;
    }

    /**
     * @return decimal
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param decimal $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getHasMoreDetails()
    {
        return $this->hasMoreDetails;
    }

    /**
     * @param string $hasMoreDetails
     */
    public function setHasMoreDetails($hasMoreDetails)
    {
        $this->hasMoreDetails = $hasMoreDetails;
    }

    /**
     * @return string
     */
    public function getMoreDetails()
    {
        return $this->moreDetails;
    }

    /**
     * @param string $moreDetails
     */
    public function setMoreDetails($moreDetails)
    {
        $this->moreDetails = $moreDetails;
    }
}
