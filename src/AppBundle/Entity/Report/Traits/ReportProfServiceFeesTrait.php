<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\Fee;
use AppBundle\Entity\Report\ProfServiceFee;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportProfServiceFeesTrait
{

    /**
     * @var string yes/no
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report", "current-prof-payments-received"})
     * @Assert\NotBlank(message="common.yesnochoice.notBlank", groups={"current-prof-payments-received"})
     */
    private $currentProfPaymentsReceived;

    /**
     * @var string yes/no
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report", "report-prof-estimate-fees"})
     * @Assert\NotBlank(message="profServiceFee.estimates.previousProfFeesEstimateGiven.notBlank", groups={"previous-prof-fees-estimate-choice"})
     */
    private $previousProfFeesEstimateGiven;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report", "report-prof-estimate-fees"})
     */
    private $profFeesEstimateSccoReason;

    /**
     * @JMS\Type("array<AppBundle\Entity\Report\ProfServiceFee>")
     * @JMS\Groups({"report-prof-service-fees"})
     *
     * @var ProfServiceFee[]
     */
    private $profServiceFees = [];


    /**
     * @return string
     */
    public function getCurrentProfPaymentsReceived()
    {
        return $this->currentProfPaymentsReceived;
    }

    /**
     * @param $currentProfPaymentsReceived
     *
     * @return $this
     */
    public function setCurrentProfPaymentsReceived($currentProfPaymentsReceived)
    {
        $this->currentProfPaymentsReceived = $currentProfPaymentsReceived;

        return $this;
    }



    /**
     * Return filtered array of ProfServiceFee's
     *
     * @param string $feeTypeId current|estimated|previous
     * @param string $fixedOrAssessed
     * @return array
     * @throws \Exception
     */
    public function getFilteredFees($feeTypeId, $fixedOrAssessed)
    {
        switch ($feeTypeId) {
            case ProfServiceFee::TYPE_CURRENT_FEE:
                $fees = $this->getProfServiceFeesByType(ProfServiceFee::TYPE_CURRENT_FEE);
                break;
            case ProfServiceFee::TYPE_ESTIMATED_FEE:
                $fees = $this->getProfServiceFeesByType(ProfServiceFee::TYPE_ESTIMATED_FEE);
                break;
            case ProfServiceFee::TYPE_PREVIOUS_FEE:
                $fees = $this->getProfServiceFeesByType(ProfServiceFee::TYPE_PREVIOUS_FEE);
                break;
            default:
                throw new \Exception('Invalid Fee type Id:' . $feeTypeId);
        }

        return array_filter($fees, function ($profServiceFee) use ($fixedOrAssessed) {
            /** @var $profServiceFee ProfServiceFee  */
            return $profServiceFee->getAssessedOrFixed() === $fixedOrAssessed;
        });
    }

    /**
     * @param string $feeTypeId "current"|"estimated"|"previous"
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getProfServiceFeesByType($feeTypeId)
    {
        if (!in_array(
            $feeTypeId,
            [
                ProfServiceFee::TYPE_CURRENT_FEE,
                ProfServiceFee::TYPE_PREVIOUS_FEE,
                ProfServiceFee::TYPE_ESTIMATED_FEE
            ]
        )) {
            throw new \Exception('Invalid feeTypeId: ' . $feeTypeId);
        }

        return array_filter($this->getProfServiceFees(), function ($profServiceFee) use ($feeTypeId) {
            /** @var $profServiceFee \AppBundle\Entity\Report\ProfServiceFee */
            return $profServiceFee->getFeeTypeId() === $feeTypeId;
        });
    }

    /**
     * Returns current Fixed service fees
     *
     * @return array
     */
    public function getCurrentFixedServiceFees()
    {
        return $this->getFilteredFees(
            ProfServiceFee::TYPE_CURRENT_FEE,
            ProfServiceFee::TYPE_FIXED_FEE
        );
    }

    /**
     * Returns current Assessed service fees
     *
     * @return array
     */
    public function getCurrentAssessedServiceFees()
    {
        return $this->getFilteredFees(
            ProfServiceFee::TYPE_CURRENT_FEE,
            ProfServiceFee::TYPE_ASSESSED_FEE
        );
    }

    /**
     * @return string
     */
    public function getPreviousProfFeesEstimateGiven()
    {
        return $this->previousProfFeesEstimateGiven;
    }

    /**
     * @param string $previousProfFeesEstimateGiven
     * @return $this
     */
    public function setPreviousProfFeesEstimateGiven($previousProfFeesEstimateGiven)
    {
        $this->previousProfFeesEstimateGiven = $previousProfFeesEstimateGiven;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfFeesEstimateSccoReason()
    {
        return $this->profFeesEstimateSccoReason;
    }

    /**
     * @param string $profFeesEstimateSccoReason
     * @return $this
     */
    public function setProfFeesEstimateSccoReason($profFeesEstimateSccoReason)
    {
        $this->profFeesEstimateSccoReason = $profFeesEstimateSccoReason;
        return $this;
    }


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
