<?php

namespace OPG\Digideps\Frontend\Entity\Report;

use OPG\Digideps\Frontend\Entity\Report\Traits\HasReportTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Fee
{
    use HasReportTrait;

    #[JMS\Groups(['fee'])]
    #[JMS\Type('int')]
    private ?int $id = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['fee'])]
    /** @phpstan-ignore property.unusedType */
    private ?string $feeTypeId = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['fee'])]
    #[Assert\Type(type: 'numeric', message: 'fee.amount.notNumeric', groups: ['fees'])]
    #[Assert\Range(notInRangeMessage: 'fee.amount.notInRangeMessage', min: 0, max: 100000000000, groups: ['fees'])]
    private ?string $amount = null;

    #[JMS\Groups(['fee'])]
    #[JMS\Type('boolean')]
    private ?bool $hasMoreDetails = null;

    #[JMS\Groups(['fee'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'fee.moreDetails.notEmpty', groups: ['fees-more-details'])]
    private ?string $moreDetails = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getFeeTypeId(): ?string
    {
        return $this->feeTypeId;
    }

    /**
     * @return ?string decimal
     */
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
