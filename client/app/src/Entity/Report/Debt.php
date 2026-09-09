<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Debt
{
    #[JMS\Type('string')]
    #[JMS\Groups(['debt'])]
    private ?string $debtTypeId;

    #[JMS\Type('string')]
    #[JMS\Groups(['debt'])]
    #[Assert\Type(type: 'numeric', message: 'debt.amount.notNumeric', groups: ['debts'])]
    #[Assert\Range(notInRangeMessage: 'debt.amount.notInRangeMessage', min: 0, max: 100000000000, groups: ['debts'])]
    private ?string $amount;

    #[JMS\Groups(['debt'])]
    #[JMS\Type('boolean')]
    private ?bool $hasMoreDetails;

    #[JMS\Groups(['debt'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'debt.moreDetails.notEmpty', groups: ['debts-more-details'])]
    private ?string $moreDetails;

    public function __construct(?string $debtTypeId, ?string $amount, ?bool $hasMoreDetails, ?string $moreDetails)
    {
        $this->debtTypeId = $debtTypeId;
        $this->amount = $amount;
        $this->hasMoreDetails = $hasMoreDetails;
        $this->moreDetails = $moreDetails;
    }

    public function getDebtTypeId(): ?string
    {
        return $this->debtTypeId;
    }

    public function setDebtTypeId(?string $debtTypeId): static
    {
        $this->debtTypeId = $debtTypeId;

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

    public function getHasMoreDetails(): ?bool
    {
        return $this->hasMoreDetails;
    }

    public function setHasMoreDetails(?bool $hasMoreDetails): static
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
}
