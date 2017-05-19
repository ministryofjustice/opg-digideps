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
     * @JMS\Groups({"fee"})
     *
     * @var string
     */
    private $hasFees;

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
     * @return string
     */
    public function getHasFees()
    {
        return $this->hasFees;
    }

    /**
     * @param string $hasFees
     */
    public function setHasFees($hasFees)
    {
        $this->hasFees = $hasFees;
    }

    /**
     * @return Fee[]
     */
    public function getFeesWithValidAmount()
    {
        return array_filter($this->fees, function ($fee) {
            return !empty($fee->getAmount());
        });
    }

    /**
     * Used to improve the section flow. see usage in Controller
     * @return boolean
     */
    public function isOtherFeesSectionComplete()
    {
        return $this->getPaidForAnything() === 'no'
        || ($this->getPaidForAnything() === 'yes' && count($this->getExpenses()));
    }
}
