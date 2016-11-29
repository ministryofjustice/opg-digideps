<?php

namespace AppBundle\Entity\Report;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

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
     * @Assert\Range(min=0, max=10000000000, minMessage = "transfer.amount.minMessage", maxMessage = "transfer.amount.maxMessage", groups={"money-transfer-amount"})
     *
     * @JMS\Type("string")
     * @JMS\Groups({"money-transfer"})
     */
    private $amount;

    /**
     * @var Account
     * @JMS\SerializedName("accountFrom")
     * @JMS\Type("AppBundle\Entity\Report\Account")
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
     * @var Account
     * @JMS\SerializedName("accountTo")
     * @JMS\Type("AppBundle\Entity\Report\Account")
     */
    private $accountTo;

    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"money-transfer"})
     *
     * @Assert\NotBlank(message="transfer.accountTo.notBlank", groups={"money-transfer-account-to"})
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
     * @return Account
     */
    public function getAccountFrom()
    {
        return $this->accountFrom;
    }

    /**
     * @return Account
     */
    public function getAccountTo()
    {
        return $this->accountTo;
    }

    /**
     * @param Account $from
     *
     * @return MoneyTransfer
     */
    public function setAccountFrom($from)
    {
        $this->accountFrom = $from;

        return $this;
    }

    /**
     * @param Account $to
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
