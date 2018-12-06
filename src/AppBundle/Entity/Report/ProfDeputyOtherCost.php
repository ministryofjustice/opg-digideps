<?php

namespace AppBundle\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class ProfDeputyOtherCost
{
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"prof-deputy-other-costs"})
     */
    private $profDeputyOtherCostTypeId;

    /**
     * @var decimal
     *
     * @JMS\Type("string")
     * @JMS\Groups({"prof-deputy-other-costs"})
     * @Assert\Type(type="numeric", message="profDeputyOtherCost.amount.notNumeric", groups={"prof-deputy-other-costs"})
     * @Assert\Range(min=0, max=100000000, minMessage = "profDeputyOtherCost.amount.minMessage", maxMessage = "profDeputyOtherCost.amount.maxMessage", groups={"prof-deputy-other-costs"})
     */
    private $amount;

    /**
     * @var string
     * @JMS\Groups({"prof-deputy-other-costs"})
     * @JMS\Type("boolean")
     */
    private $hasMoreDetails;

    /**
     * @var string
     * @JMS\Groups({"prof-deputy-other-costs"})
     * @JMS\Type("string")
     *
     * @Assert\NotBlank(message="profDeputyOtherCost.moreDetails.notEmpty", groups={"prof-deputy-other-cost-more-details"})
     */
    private $moreDetails;

    /**
     * ProfDeputyOtherCost constructor.
     *
     * @param $profDeputyOtherCostTypeId
     * @param decimal $amount
     * @param string  $hasMoreDetails
     * @param string  $moreDetails
     */
    public function __construct($profDeputyOtherCostTypeId, $amount, $hasMoreDetails, $moreDetails)
    {
        $this->profDeputyOtherCostTypeId = $profDeputyOtherCostTypeId;
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
     * @param $profDeputyOtherCostTypeIds
     */
    public static function setProfDeputyOtherCostTypeIds($profDeputyOtherCostTypeIds)
    {
        self::$profDeputyOtherCostTypeIds = $profDeputyOtherCostTypeIds;
    }

    /**
     * @return mixed
     */
    public function getProfDeputyOtherCostTypeId()
    {
        return $this->profDeputyOtherCostTypeId;
    }

    /**
     * @param $profDeputyOtherCostTypeId
     */
    public function setProfDeputyOtherCostTypeId($profDeputyOtherCostTypeId)
    {
        $this->profDeputyOtherCostTypeId = $profDeputyOtherCostTypeId;
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
