<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\Traits\HasBankAccountTrait;
use OPG\Digideps\Frontend\Entity\Report\Traits\HasReportTrait;
use Symfony\Component\Validator\Constraints as Assert;

class Expense
{
    use HasReportTrait;
    use HasBankAccountTrait;

    #[JMS\Type('integer')]
    #[JMS\Groups(['expenses'])]
    private ?int $id = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['expenses'])]
    #[Assert\NotBlank(message: 'expenses.explanation.notBlank', groups: ['deputy-expense'])]
    private ?string $explanation = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['expenses'])]
    #[Assert\NotBlank(message: 'expenses.amount.notBlank', groups: ['deputy-expense'])]
    #[Assert\Type(type: 'numeric', message: 'expenses.amount.type', groups: ['deputy-expense'])]
    #[Assert\Range(notInRangeMessage: 'expenses.amount.notInRangeMessage', min: 0.01, max: 100000000000, groups: ['deputy-expense'])]
    private ?string $amount = null;

    /**
     * @JMS\Type("DateTime")
     * @JMS\Groups({"expenses"})
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

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }
}
