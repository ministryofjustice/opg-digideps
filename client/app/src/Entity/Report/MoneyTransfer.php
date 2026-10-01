<?php

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * MoneyTransfer.
 */
class MoneyTransfer
{
    /**
     * @var int
     */
    #[JMS\Type('integer')]
    private $id;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['money-transfer'])]
    #[Assert\NotBlank(message: 'transfer.amount.notBlank', groups: ['money-transfer-amount'])]
    #[Assert\Range(notInRangeMessage: 'transfer.amount.notInRangeMessage', min: 0, max: 100000000000, groups: ['money-transfer-amount'])]
    private $amount;

    /**
     * @var BankAccount
     */
    #[JMS\SerializedName('accountFrom')]
    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\BankAccount')]
    private $accountFrom;

    #[JMS\Type('integer')]
    #[JMS\Groups(['money-transfer'])]
    #[Assert\NotBlank(message: 'transfer.accountFrom.notBlank', groups: ['money-transfer-account-from'])]
    private $accountFromId;

    /**
     * @var BankAccount
     */
    #[JMS\SerializedName('accountTo')]
    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\BankAccount')]
    private $accountTo;

    #[JMS\Type('integer')]
    #[JMS\Groups(['money-transfer'])]
    #[Assert\NotBlank(message: 'transfer.accountTo.notBlank', groups: ['money-transfer-account-to'])]
    #[Assert\Expression("(value == '' or value != this.getAccountFromId() )", message: 'transfer.accountTo.sameAsFromAccount', groups: ['money-transfer-account-to'])]
    private $accountToId;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['money-transfer'])]
    private $description;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set amount.
     *
     * @param string $amount
     */
    public function setAmount($amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount.
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return BankAccount
     */
    public function getAccountFrom()
    {
        return $this->accountFrom;
    }

    /**
     * @return BankAccount
     */
    public function getAccountTo()
    {
        return $this->accountTo;
    }

    /**
     * @param BankAccount $from
     */
    public function setAccountFrom($from): static
    {
        $this->accountFrom = $from;

        return $this;
    }

    /**
     * @param BankAccount $to
     */
    public function setAccountTo($to): static
    {
        $this->accountTo = $to;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccountFromId()
    {
        return $this->accountFromId;
    }

    /**
     * @param mixed $accountFromId
     */
    public function setAccountFromId($accountFromId): static
    {
        $this->accountFromId = $accountFromId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccountToId()
    {
        return $this->accountToId;
    }

    /**
     * @param mixed $accountToId
     */
    public function setAccountToId($accountToId): static
    {
        $this->accountToId = $accountToId;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description): static
    {
        $this->description = $description;

        return $this;
    }
}
