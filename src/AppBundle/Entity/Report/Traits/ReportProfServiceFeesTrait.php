<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\Fee;
use AppBundle\Entity\Report\ProfServiceFee;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

trait ReportProfServiceFeesTrait
{
    /**
     * @JMS\Type("array<AppBundle\Entity\Report\ProfServiceFee>")
     * @JMS\Groups({"report-prof-service-fees"})
     *
     * @var ProfServiceFee[]
     */
    private $profServiceFees = [];

    /**
     * @return ProfServiceFee[]
     */
    public function getProfServiceFees()
    {
        return $this->profServiceFees;
    }

    /**
     * @param ProfServiceFee[] $profServiceFees
     */
    public function setProfServiceFees($profServiceFees)
    {
        $this->profServiceFees = $profServiceFees;
    }

    /**
     * @return ProfServiceFee[]
     */
    public function getCurrentProfServiceFees()
    {
        return array_filter($this->getProfServiceFees(), function($profServiceFee) {
            return $profServiceFee->isCurrentFee();
        });
    }

    /**
     * Has Report got profServiceFee?
     *
     * @param int $id
     *
     * @return bool
     */
    public function hasProfServiceFeeWithId($id)
    {
        foreach ($this->getProfServiceFees() as $profServiceFee) {
            if ($profServiceFee->getId() == $id) {
                return true;
            }
        }

        return false;
    }

    public function getFeeTotals()
    {

        $fixedServiceFees = $this->getFilteredFees(
            ProfServiceFee::TYPE_CURRENT_FEE,
            ProfServiceFee::TYPE_FIXED_FEE
        );
        $assessedServiceFees = $this->getFilteredFees(
            ProfServiceFee::TYPE_CURRENT_FEE,
            ProfServiceFee::TYPE_ASSESSED_FEE
        );

        $feeTotals = [];
        $feeTotals['totalFixedFeesReceived'] = $this->getTotalReceivedFees($fixedServiceFees);
        $feeTotals['totalFixedFeesCharged'] = $this->getTotalChargedFees($fixedServiceFees);
        $feeTotals['totalAssessedFeesReceived'] = $this->getTotalReceivedFees($assessedServiceFees);
        $feeTotals['totalAssessedFeesCharged'] = $this->getTotalChargedFees($assessedServiceFees);

        return $feeTotals;
    }

    /**
     * Calculate total Received Fees
     *
     * @param array $profFees
     * @return float
     */
    private function getTotalReceivedFees(array $profFees)
    {
        $total = 0.00;

        foreach($profFees as $profFee)
        {
            /**  @var ProfServiceFee $profFee */
            $total += $profFee->getAmountReceived();
        }

        return $total;
    }

    /**
     * Calculate total Charged Fees
     *
     * @param array $profFees
     * @return float
     */
    private function getTotalChargedFees(array $profFees)
    {
        $total = 0.00;

        foreach($profFees as $profFee)
        {
            /**  @var ProfServiceFee $profFee */
            $total += $profFee->getAmountCharged();
        }

        return $total;
    }
}
