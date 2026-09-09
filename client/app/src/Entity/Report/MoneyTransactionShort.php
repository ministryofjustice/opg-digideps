<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class MoneyTransactionShort
{
    #[JMS\Type('integer')]
    #[JMS\Groups(['moneyTransactionShort'])]
    private ?int $id = null;

    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\Report')]
    private ?Report $report = null;

    #[JMS\Type('double')]
    #[JMS\Groups(['moneyTransactionShort'])]
    #[Assert\NotBlank(message: 'moneyTransactionShort.amount.notBlank', groups: ['money-transaction-short'])]
    #[Assert\Type(type: 'numeric', message: 'moneyTransactionShort.amount.type', groups: ['money-transaction-short'])]
    #[Assert\Range(notInRangeMessage: 'moneyTransactionShort.amount.notInRangeMessage', min: 1000, max: 10000000, groups: ['money-transaction-short'])]
    private ?float $amount = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['moneyTransactionShort'])]
    #[Assert\NotBlank(message: 'moneyTransactionShort.description.notBlank', groups: ['money-transaction-short'])]
    private ?string $description = null;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['moneyTransactionShort'])]
    #[Assert\Type(type: 'DateTimeInterface', message: 'moneyTransactionShort.date.notValid', groups: ['money-transaction-short'])]
    private ?\DateTime $date = null;

    /**
     * Discriminator field.
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['moneyTransactionShort'])]
    private string $type;

    public function __construct(string $type)
    {
        $this->setType($type);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getReport(): ?Report
    {
        return $this->report;
    }

    public function setReport(?Report $report): static
    {
        $this->report = $report;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }
}
