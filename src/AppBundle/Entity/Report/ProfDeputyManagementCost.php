<?php

namespace AppBundle\Entity\Report;

class ProfDeputyManagementCost
{
    private $profDeputyManagementCostTypeId;
    private $amount;

    public function __construct($profDeputyManagementCostTypeId, $amount)
    {
        $this->profDeputyManagementCostTypeId = $profDeputyManagementCostTypeId;
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
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
}