<?php

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportBalanceTrait
{
    /**
     *
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['balance', 'balance_mismatch_explanation'])]
    #[Assert\NotBlank(message: 'report.balanceMismatchExplanation.notBlank', groups: ['balance'])]
    #[Assert\Length(min: 10, minMessage: 'report.balanceMismatchExplanation.length', groups: ['balance'])]
    private $balanceMismatchExplanation;

    /**
     * @var float
     */
    #[JMS\Type('double')]
    private $totalsOffset;

    /**
     * @var bool
     */
    #[JMS\Type('boolean')]
    private $totalsMatch;

    /**
     * @return string
     */
    public function getBalanceMismatchExplanation()
    {
        return $this->balanceMismatchExplanation;
    }

    /**
     * @param string $balanceMismatchExplanation
     */
    public function setBalanceMismatchExplanation($balanceMismatchExplanation): static
    {
        $this->balanceMismatchExplanation = $balanceMismatchExplanation;

        return $this;
    }

    /**
     * @var float
     */
    #[JMS\Type('double')]
    private $calculatedBalance;

    /**
     * @return float
     */
    public function getCalculatedBalance()
    {
        return $this->calculatedBalance;
    }

    /**
     * @param float $calculatedBalance
     */
    public function setCalculatedBalance($calculatedBalance): static
    {
        $this->calculatedBalance = $calculatedBalance;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalsOffset()
    {
        return $this->totalsOffset;
    }

    /**
     * @param float $totalsOffset
     */
    public function setTotalsOffset($totalsOffset): static
    {
        $this->totalsOffset = $totalsOffset;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTotalsMatch()
    {
        return $this->totalsMatch;
    }

    /**
     * @param bool $totalsMatch
     */
    public function setTotalsMatch($totalsMatch): void
    {
        $this->totalsMatch = $totalsMatch;
    }
}
