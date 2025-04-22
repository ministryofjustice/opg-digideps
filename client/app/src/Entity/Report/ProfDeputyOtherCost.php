<?php

namespace App\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @Assert\Callback(callback="moreDetailsValidate", groups={"prof-deputy-other-costs"})
 */
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
     * @Assert\Range(min=0, max=100000000000, notInRangeMessage = "profDeputyOtherCost.amount.notInRangeMessage", groups={"prof-deputy-other-costs"})
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

    public function moreDetailsValidate(ExecutionContextInterface $context)
    {
        if (!$this->getHasMoreDetails()) {
            return;
        }

        $hasMoreDetails = trim($this->getMoreDetails(), " \n") ? true : false;

        if ($this->getAmount() && !$hasMoreDetails) {
            $context->buildViolation('profDeputyOtherCost.moreDetails.notBlank')->atPath('moreDetails')->addViolation();
        }
    }
}
