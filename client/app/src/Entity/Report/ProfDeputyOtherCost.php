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
    private ?string $profDeputyOtherCostTypeId;

    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-other-costs'])]
    #[Assert\Type(type: 'numeric', message: 'profDeputyOtherCost.amount.notNumeric', groups: ['prof-deputy-other-costs'])]
    #[Assert\Range(notInRangeMessage: 'profDeputyOtherCost.amount.notInRangeMessage', min: 0, max: 100000000000, groups: ['prof-deputy-other-costs'])]
    private ?string $amount;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['prof-deputy-other-costs'])]
    private bool $hasMoreDetails;

    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-other-costs'])]
    private ?string $moreDetails;

    /**
     * ProfDeputyOtherCost constructor.
     *
     * @param ?string $profDeputyOtherCostTypeId
     * @param ?string $amount decimal
     * @param bool $hasMoreDetails
     * @param ?string $moreDetails
     */
    public function __construct(?string $profDeputyOtherCostTypeId, ?string $amount, bool $hasMoreDetails, ?string $moreDetails)
    {
        $this->profDeputyOtherCostTypeId = $profDeputyOtherCostTypeId;
        $this->amount = $amount;
        $this->hasMoreDetails = $hasMoreDetails;
        $this->moreDetails = $moreDetails;
    }

    public function getProfDeputyOtherCostTypeId(): ?string
    {
        return $this->profDeputyOtherCostTypeId;
    }

    public function setProfDeputyOtherCostTypeId(?string $profDeputyOtherCostTypeId): static
    {
        $this->profDeputyOtherCostTypeId = $profDeputyOtherCostTypeId;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    /**
     * @param ?string $amount decimal
     */
    public function setAmount(?string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getHasMoreDetails(): bool
    {
        return $this->hasMoreDetails;
    }

    public function setHasMoreDetails(bool $hasMoreDetails): static
    {
        $this->hasMoreDetails = $hasMoreDetails;

        return $this;
    }

    public function getMoreDetails(): ?string
    {
        return $this->moreDetails;
    }

    public function setMoreDetails(?string $moreDetails): static
    {
        $this->moreDetails = $moreDetails;

        return $this;
    }

    public function moreDetailsValidate(ExecutionContextInterface $context): bool
    {
        if ($this->getHasMoreDetails()) {
            $hasMoreDetails = trim($this->getMoreDetails() ?? '', " \n");

            if ($this->getAmount() && empty($hasMoreDetails)) {
                $context->buildViolation('profDeputyOtherCost.moreDetails.notBlank')->atPath('moreDetails')->addViolation();
                return false;
            }
        }

        return true;
    }
}
