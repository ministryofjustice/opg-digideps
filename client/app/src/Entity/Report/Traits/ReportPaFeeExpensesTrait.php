<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\Fee;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait ReportPaFeeExpensesTrait
{
    /**
     * @var array<Fee>
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\Fee>')]
    #[JMS\Groups(['fee'])]
    private array $fees = [];

    #[JMS\Type('string')]
    #[JMS\Groups(['reasonForNoFees'])]
    #[Assert\NotBlank(message: 'fee.reasonForNoFees.notBlank', groups: ['reasonForNoFees'])]
    private ?string $reasonForNoFees = null;

    /**
     * @var ?string 'yes'|'no'|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['fee'])]
    private ?string $hasFees = null;

    #[JMS\Type('double')]
    private ?float $feesTotal = null;

    /**
     * @return array<Fee>
     */
    public function getFees(): array
    {
        return $this->fees;
    }

    /**
     * @param array<Fee> $fees
     */
    public function setFees(array $fees): static
    {
        $this->fees = $fees;
        return $this;
    }

    public function getReasonForNoFees(): ?string
    {
        return $this->reasonForNoFees;
    }

    public function setReasonForNoFees(?string $reasonForNoFees): static
    {
        $this->reasonForNoFees = $reasonForNoFees;

        return $this;
    }

    public function getFeesTotal(): ?float
    {
        return $this->feesTotal;
    }

    public function setFeesTotal(?float $feesTotal): static
    {
        $this->feesTotal = $feesTotal;
        return $this;
    }

    public function feesValid(ExecutionContextInterface $context): void
    {
        if (empty($this->getReasonForNoFees()) && count($this->getFeesWithValidAmount()) === 0) {
            $context->addViolation('fee.mustHaveAtLeastOneFee');
        }
    }

    public function getHasFees(): ?string
    {
        return $this->hasFees;
    }

    public function setHasFees(?string $hasFees): static
    {
        $this->hasFees = $hasFees;
        return $this;
    }

    /**
     * @return array<Fee>
     */
    public function getFeesWithValidAmount(): array
    {
        return array_filter($this->fees, function ($fee): bool {
            return !empty($fee->getAmount());
        });
    }

    /**
     * Used to improve the section flow.
     */
    public function isOtherFeesSectionComplete(): bool
    {
        return $this->getPaidForAnything() === 'no'
            || ($this->getPaidForAnything() === 'yes' && count($this->getExpenses()));
    }
}
