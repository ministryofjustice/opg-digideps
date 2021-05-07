<?php

namespace App\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * MoneyTransfer.
 */
class MoneyTransfer
{
    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(message="transfer.amount.notBlank", groups={"money-transfer-amount"})
     * @Assert\Range(min=0, max=100000000000, notInRangeMessage = "transfer.amount.notInRangeMessage", groups={"money-transfer-amount"})
     *
     * @JMS\Type("string")
     * @JMS\Groups({"money-transfer"})
     */
    private $amount;

    /**
     * @var BankAccount
     * @JMS\SerializedName("accountFrom")
     * @JMS\Type("App\Entity\Report\BankAccount")
     */
    private $accountFrom;

    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"money-transfer"})
     *
     * @Assert\NotBlank(message="transfer.accountFrom.notBlank", groups={"money-transfer-account-from"})
     */
    private $accountFromId;

    /**
     * @var BankAccount
     * @JMS\SerializedName("accountTo")
     * @JMS\Type("App\Entity\Report\BankAccount")
     */
    private $accountTo;

    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"money-transfer"})
     *
     * @Assert\NotBlank(message="transfer.accountTo.notBlank", groups={"money-transfer-account-to"})
     * @Assert\Expression(
     *     "(value == '' or value != this.getAccountFromId() )",
     *     message="transfer.accountTo.sameAsFromAccount",
     *     groups={"money-transfer-account-to"}
     * )
     */
    private $accountToId;

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
     *
     * @return MoneyTransfer
     */
    public function setAmount($amount)
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
     *
     * @return MoneyTransfer
     */
    public function setAccountFrom($from)
    {
        $this->accountFrom = $from;

        return $this;
    }

    /**
     * @param BankAccount $to
     *
     * @return MoneyTransfer
     */
    public function setAccountTo($to)
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
    public function setAccountFromId($accountFromId)
    {
        $this->accountFromId = $accountFromId;
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
    public function setAccountToId($accountToId)
    {
        $this->accountToId = $accountToId;
    }
}
