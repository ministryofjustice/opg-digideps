<?php

namespace AppBundle\Entity\Report;


class ProfDeputyManagementCost
{
    /**
     * ProfDeputyManagementCost constructor.
     *
     * @param decimal $amount
     */
    public function __construct($amount)
    {
        $this->amount = $amount;
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
}