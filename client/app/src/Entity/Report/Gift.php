<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\Traits\HasBankAccountTrait;
use OPG\Digideps\Frontend\Entity\Report\Traits\HasReportTrait;
use Symfony\Component\Validator\Constraints as Assert;

class Gift
{
    use HasReportTrait;
    use HasBankAccountTrait;

    #[JMS\Type('integer')]
    #[JMS\Groups(['gift'])]
    private ?int $id = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['gift'])]
    #[Assert\NotBlank(message: 'gifts.explanation.notBlank', groups: ['gift'])]
    private ?string $explanation = null;

    #[JMS\Type('double')]
    #[JMS\Groups(['gift'])]
    #[Assert\NotBlank(message: 'gifts.amount.notBlank', groups: ['gift'])]
    #[Assert\Type(type: 'numeric', message: 'gifts.amount.type', groups: ['gift'])]
    #[Assert\Range(notInRangeMessage: 'gifts.amount.notInRangeMessage', min: 0.01, max: 100000000000, groups: ['gift'])]
    private float $amount = 0.0;

    /**
     * @JMS\Type("DateTime")
     * @JMS\Groups({"gift"})
     * @phpstan-ignore property.unusedType
     */
    private ?\DateTime $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getExplanation(): ?string
    {
        return $this->explanation;
    }

    public function setExplanation(?string $explanation): static
    {
        $this->explanation = $explanation;

        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }
}
