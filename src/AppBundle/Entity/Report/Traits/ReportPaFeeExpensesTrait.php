<?php

namespace AppBundle\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;
use AppBundle\Entity\Report\Fee;

trait ReportPaFeeExpensesTrait
{
    /**
     * @JMS\Type("array<AppBundle\Entity\Report\Fee>")
     * @JMS\Groups({"fee"})
     *
     * @var Fee[]
     */
    private $fees;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"reasonForNoFees"})
     *
     * @Assert\NotBlank(message="fee.reasonForNoFees.notBlank", groups={"reasonForNoFees"})
     *
     * @var string
     */
    private $reasonForNoFees;

    /**
     * @JMS\Type("string")
     *
     * @var decimal
     */
    private $feesTotalAmount;

    /**
     * @return ArrayCollection
     */
    public function getFees()
    {
        return $this->fees;
    }

    /**
     * @param Fee[] $fees
     */
    public function setFees($fees)
    {
        $this->fees = $fees;
    }

    /**
     * @return string
     */
    public function getReasonForNoFees()
    {
        return $this->reasonForNoFees;
    }

    /**
     * @param string $reasonForNoFees
     */
    public function setReasonForNoFees($reasonForNoFees)
    {
        $this->reasonForNoFees = $reasonForNoFees;
    }

    /**
     * @return decimal
     */
    public function getFeesTotalAmount()
    {
        return $this->feesTotalAmount;
    }

    public function hasFees()
    {
        if (empty($this->getFees()) && $this->getReasonForNoFees() === null) {
            return null;
        }

        return $this->getReasonForNoFees() ? 'no' : 'yes';
    }

    public function setHasFees($value)
    {
        // necessary to simplify form logic
        return null;
    }

    /**
     * @param decimal $feesTotalAmount
     */
    public function setFeesTotalAmount($feesTotalAmount)
    {
        $this->feesTotalAmount = $feesTotalAmount;
    }


    /**
     * @param ExecutionContextInterface $context
     */
    public function feesValid(ExecutionContextInterface $context)
    {
        if (empty($this->getReasonForNoFees()) && count($this->getFeesWithValidAmount()) === 0) {
            $context->addViolation('fee.mustHaveAtLeastOneFee');
        }
    }


    /**
     * Get debts total value.
     *
     * @return float
     */
//    public function getFeesTotalValue()
//    {
//        $ret = 0;
//        foreach ($this->getFeesTotalValue() as $debt) {
//            $ret += $debt->getAmount();
//        }
//
//        return $ret;
//    }

    /**
     * @param $debtId
     *
     * @return Debt|null
     */
//    public function getDebtById($debtId)
//    {
//        foreach ($this->getDebts() as $debt) {
//            if ($debt->getDebtTypeId() == $debtId) {
//                return $debt;
//            }
//        }
//
//        return null;
//    }



    /**
     * @return decimal
     */
//    public function getDebtsTotalAmount()
//    {
//        return $this->debtsTotalAmount;
//    }
//
//    /**
//     * @param decimal $debtsTotalAmount
//     */
//    public function setDebtsTotalAmount($debtsTotalAmount)
//    {
//        $this->debtsTotalAmount = $debtsTotalAmount;
//
//        return $this;
//    }


    /**
     * @param ExecutionContextInterface $context
     */
//    public function debtsValid(ExecutionContextInterface $context)
//    {
//        if ($this->getHasDebts() == 'yes' && count($this->getDebtsWithValidAmount()) === 0) {
//            $context->addViolation('report.hasDebts.mustHaveAtLeastOneDebt');
//        }
//    }

    /**
     * @return Fee[]
     */
    public function getFeesWithValidAmount()
    {
        return array_filter($this->fees, function ($fee) {
            return !empty($fee->getAmount());
        });
    }
}
