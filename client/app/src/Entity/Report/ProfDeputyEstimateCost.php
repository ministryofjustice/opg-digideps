<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[Assert\Callback(callback: 'moreDetailsValidate', groups: ['prof-deputy-estimate-costs'])]
class ProfDeputyEstimateCost
{
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-estimate-costs'])]
    private ?string $profDeputyEstimateCostTypeId;

    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-estimate-costs'])]
    #[Assert\Type(type: 'numeric', message: 'profDeputyEstimateCost.amount.notNumeric', groups: ['prof-deputy-estimate-costs'])]
    #[Assert\Range(notInRangeMessage: 'profDeputyEstimateCost.amount.notInRangeMessage', min: 0, max: 100000000, groups: ['prof-deputy-estimate-costs'])]
    private ?string $amount;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['prof-deputy-estimate-costs'])]
    private bool|string $hasMoreDetails;

    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-estimate-costs'])]
    private ?string $moreDetails;

    public function __construct(
        ?string $profDeputyEstimateCostTypeId,
        ?string $amount,
        bool|string $hasMoreDetails,
        ?string $moreDetails = null
    ) {
        $this->profDeputyEstimateCostTypeId = $profDeputyEstimateCostTypeId;
        $this->amount = $amount;
        $this->hasMoreDetails = $hasMoreDetails;
        $this->moreDetails = $moreDetails;
    }

    public function getProfDeputyEstimateCostTypeId(): ?string
    {
        return $this->profDeputyEstimateCostTypeId;
    }

    public function setProfDeputyEstimateCostTypeId(string $profDeputyEstimateCostTypeId): static
    {
        $this->profDeputyEstimateCostTypeId = $profDeputyEstimateCostTypeId;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getHasMoreDetails(): bool|string
    {
        return $this->hasMoreDetails;
    }

    public function setHasMoreDetails(string $hasMoreDetails): static
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
                $context->buildViolation('profDeputyEstimateCost.moreDetails.notBlank')->atPath('moreDetails')->addViolation();
                return false;
            }
        }

        return true;
    }
}
