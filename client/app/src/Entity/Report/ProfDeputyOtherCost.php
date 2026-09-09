<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[Assert\Callback(callback: 'moreDetailsValidate', groups: ['prof-deputy-other-costs'])]
class ProfDeputyOtherCost
{
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-other-costs'])]
    private string $profDeputyOtherCostTypeId;

    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-other-costs'])]
    #[Assert\Type(type: 'numeric', message: 'profDeputyOtherCost.amount.notNumeric', groups: ['prof-deputy-other-costs'])]
    #[Assert\Range(notInRangeMessage: 'profDeputyOtherCost.amount.notInRangeMessage', min: 0, max: 100000000000, groups: ['prof-deputy-other-costs'])]
    private float $amount;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['prof-deputy-other-costs'])]
    private bool|string $hasMoreDetails;

    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-other-costs'])]
    private ?string $moreDetails;

    public function __construct(string $profDeputyOtherCostTypeId, float $amount, bool $hasMoreDetails, ?string $moreDetails)
    {
        $this->profDeputyOtherCostTypeId = $profDeputyOtherCostTypeId;
        $this->amount = $amount;
        $this->hasMoreDetails = $hasMoreDetails;
        $this->moreDetails = $moreDetails;
    }

    public function getProfDeputyOtherCostTypeId(): string
    {
        return $this->profDeputyOtherCostTypeId;
    }

    public function setProfDeputyOtherCostTypeId(string $profDeputyOtherCostTypeId): static
    {
        $this->profDeputyOtherCostTypeId = $profDeputyOtherCostTypeId;
        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    public function getHasMoreDetails(): bool|string
    {
        return $this->hasMoreDetails;
    }

    public function setHasMoreDetails(bool|string $hasMoreDetails): static
    {
        $this->hasMoreDetails = $hasMoreDetails;
        return $this;
    }

    public function getMoreDetails(): ?string
    {
        return $this->moreDetails;
    }

    public function setMoreDetails(string $moreDetails): static
    {
        $this->moreDetails = $moreDetails;
        return $this;
    }

    public function moreDetailsValidate(ExecutionContextInterface $context): void
    {
        $hasMoreDetailsValue = $this->getHasMoreDetails();
        $hasNoMoreDetails = $hasMoreDetailsValue === 'no' || $hasMoreDetailsValue === false;

        $moreDetailsExists = strlen(trim($this->getMoreDetails() ?? '', " \n")) > 0;

        if ($hasNoMoreDetails && $moreDetailsExists && $this->getAmount()) {
            $context->buildViolation('profDeputyOtherCost.moreDetails.notBlank')->atPath('moreDetails')->addViolation();
        }
    }
}
