<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class MoneyTransfer
{
    #[JMS\Type('integer')]
    /** @phpstan-ignore property.unusedType */
    private ?int $id = null;

    #[JMS\Type('double')]
    #[JMS\Groups(['money-transfer'])]
    #[Assert\NotBlank(message: 'transfer.amount.notBlank', groups: ['money-transfer-amount'])]
    #[Assert\Range(notInRangeMessage: 'transfer.amount.notInRangeMessage', min: 0, max: 100000000000, groups: ['money-transfer-amount'])]
    private ?float $amount = null;

    #[JMS\SerializedName('accountFrom')]
    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\BankAccount')]
    private BankAccount $accountFrom;

    #[JMS\Type('integer')]
    #[JMS\Groups(['money-transfer'])]
    #[Assert\NotBlank(message: 'transfer.accountFrom.notBlank', groups: ['money-transfer-account-from'])]
    private ?int $accountFromId = null;

    #[JMS\SerializedName('accountTo')]
    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\BankAccount')]
    private BankAccount $accountTo;

    #[JMS\Type('integer')]
    #[JMS\Groups(['money-transfer'])]
    #[Assert\NotBlank(message: 'transfer.accountTo.notBlank', groups: ['money-transfer-account-to'])]
    #[Assert\Expression("(value == '' or value != this.getAccountFromId() )", message: 'transfer.accountTo.sameAsFromAccount', groups: ['money-transfer-account-to'])]
    private ?int $accountToId = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['money-transfer'])]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setAmount(?float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function getAccountFrom(): BankAccount
    {
        return $this->accountFrom;
    }

    public function getAccountTo(): BankAccount
    {
        return $this->accountTo;
    }

    public function setAccountFrom(BankAccount $from): static
    {
        $this->accountFrom = $from;

        return $this;
    }

    public function setAccountTo(BankAccount $to): static
    {
        $this->accountTo = $to;

        return $this;
    }

    public function getAccountFromId(): ?int
    {
        return $this->accountFromId ?? $this->accountFrom->getId();
    }

    public function setAccountFromId(?int $accountFromId): static
    {
        $this->accountFromId = $accountFromId;

        return $this;
    }

    public function getAccountToId(): ?int
    {
        return $this->accountToId ?? $this->accountTo->getId();
    }

    public function setAccountToId(?int $accountToId): static
    {
        $this->accountToId = $accountToId;

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
}
