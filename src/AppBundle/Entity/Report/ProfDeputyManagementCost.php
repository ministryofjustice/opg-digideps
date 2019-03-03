<?php

namespace AppBundle\Entity\Report;


class ProfDeputyManagementCost
{
    /**
     * ProfDeputyManagementCost constructor.
     *
     * @param $profDeputyManagementCostTypeId
     * @param decimal $amount
     * @param string  $hasMoreDetails
     * @param string  $moreDetails
     */
    public function __construct($profDeputyManagementCostTypeId, $amount, $hasMoreDetails, $moreDetails)
    {
        $this->profDeputyManagementCostTypeId = $profDeputyManagementCostTypeId;
        $this->amount = $amount;
        $this->hasMoreDetails = $hasMoreDetails;
        $this->moreDetails = $moreDetails;
    }

    /**
     * @var decimal
     *
     * @JMS\Type("string")
     * @JMS\Groups({"prof-deputy-management-costs"})
     * @Assert\Type(type="numeric", message="profDeputyEstimateCost.amount.notNumeric", groups={"prof-deputy-estimate-costs"})
     * @Assert\Range(min=0, max=100000000, minMessage = "profDeputyEstimateCost.amount.minMessage", maxMessage = "profDeputyEstimateCost.amount.maxMessage", groups={"prof-deputy-estimate-costs"})
     */
    private $amount;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"prof-deputy-management-costs"})
     */
    private $profDeputyManagementCostTypeId;

    /**
     * @var string
     * @JMS\Groups({"prof-deputy-management-costs"})
     * @JMS\Type("boolean")
     */
    private $hasMoreDetails;

    /**
     * @var string
     * @JMS\Groups({"prof-deputy-management-costs"})
     * @JMS\Type("string")
     */
    private $moreDetails;

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
     * @return mixed
     */
    public function getProfDeputyManagementCostTypeId()
    {
        return $this->profDeputyManagementCostTypeId;
    }

    /**
     * @param mixed $profDeputyManagementCostTypeId
     */
    public function setProfDeputyManagementCostTypeId($profDeputyManagementCostTypeId)
    {
        $this->profDeputyManagementCostTypeId = $profDeputyManagementCostTypeId;
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