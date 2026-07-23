<?php

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use OPG\Digideps\Frontend\Entity\Report\Fee;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait ReportPaFeeExpensesTrait
{
    /**
     *
     * @var Fee[]
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\Fee>')]
    #[JMS\Groups(['fee'])]
    private $fees = [];

    /**
     *
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['reasonForNoFees'])]
    #[Assert\NotBlank(message: 'fee.reasonForNoFees.notBlank', groups: ['reasonForNoFees'])]
    private $reasonForNoFees;

    /**
     *
     * @var string $hasFees
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['fee'])]
    private $hasFees;

    /**
     * @var string $feesTotal
     */
    #[JMS\Type('double')]
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
    public function setFees($fees): void
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
    public function setReasonForNoFees($reasonForNoFees): void
    {
        $this->reasonForNoFees = $reasonForNoFees;
    }

    /**
     * @return string
     */
    public function getFeesTotal()
    {
        return $this->feesTotal;
    }

    /**
     * @param string $feesTotal
     */
    public function setFeesTotal($feesTotal): void
    {
        $this->feesTotal = $feesTotal;
    }

    public function feesValid(ExecutionContextInterface $context): void
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
    public function setHasFees($hasFees): void
    {
        $this->hasFees = $hasFees;
    }

    /**
     * @return Fee[]
     */
    public function getFeesWithValidAmount(): array
    {
        return array_filter($this->fees, function ($fee): bool {
            return !empty($fee->getAmount());
        });
    }

    /**
     * Used to improve the section flow. see usage in Controller.
     *
     * @return bool
     */
    public function isOtherFeesSectionComplete()
    {
        return $this->getPaidForAnything() === 'no'
        || ($this->getPaidForAnything() === 'yes' && count($this->getExpenses()));
    }
}
