<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\Fee;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

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
     * @JMS\Type("double")
     *
     * @var decimal
     */
    private $feesTotal;

    /**
     * @return Fee[]
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
    public function getFeesTotal()
    {
        return $this->feesTotal;
    }

    /**
     * @param decimal $feesTotal
     */
    public function setFeesTotal($feesTotal)
    {
        $this->feesTotal = $feesTotal;
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
     * @return bool
     */
    public function isOtherFeesSectionComplete()
    {
        return $this->getPaidForAnything() === 'no'
        || ($this->getPaidForAnything() === 'yes' && count($this->getExpenses()));
    }
}
