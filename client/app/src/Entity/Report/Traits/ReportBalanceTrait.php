<?php

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportBalanceTrait
{
    #[JMS\Type('string')]
    #[JMS\Groups(['balance', 'balance_mismatch_explanation'])]
    #[Assert\NotBlank(message: 'report.balanceMismatchExplanation.notBlank', groups: ['balance'])]
    #[Assert\Length(min: 10, minMessage: 'report.balanceMismatchExplanation.length', groups: ['balance'])]
    private string $balanceMismatchExplanation;

    #[JMS\Type('double')]
    private float $totalsOffset;

    #[JMS\Type('boolean')]
    private bool $totalsMatch;

    #[JMS\Type('double')]
    private float $calculatedBalance;

    public function getBalanceMismatchExplanation(): string
    {
        return $this->balanceMismatchExplanation;
    }

    public function setBalanceMismatchExplanation(string $balanceMismatchExplanation): static
    {
        $this->balanceMismatchExplanation = $balanceMismatchExplanation;

        return $this;
    }

    public function getCalculatedBalance(): float
    {
        return $this->calculatedBalance;
    }

    public function setCalculatedBalance(float $calculatedBalance): static
    {
        $this->calculatedBalance = $calculatedBalance;

        return $this;
    }

    public function getTotalsOffset(): float
    {
        return $this->totalsOffset;
    }

    public function setTotalsOffset(float $totalsOffset): static
    {
        $this->totalsOffset = $totalsOffset;

        return $this;
    }

    public function isTotalsMatch(): bool
    {
        return $this->totalsMatch;
    }

    public function setTotalsMatch(bool $totalsMatch): static
    {
        $this->totalsMatch = $totalsMatch;

        return $this;
    }
}
