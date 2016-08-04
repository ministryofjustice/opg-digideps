<?php

namespace AppBundle\Entity;

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
     * @Assert\NotBlank(message="transfer.amount.notBlank")
     * @Assert\Range(min=0, max=10000000000, minMessage = "transfer.amount.minMessage", maxMessage = "transfer.amount.maxMessage")
     * @JMS\Type("string")
     */
    private $amount;

    /**
     * @var Account
     * @JMS\SerializedName("accountFrom")
     * @JMS\Type("AppBundle\Entity\Account")
     */
    private $accountFrom;

    /**
     * @JMS\Type("integer")
     * @Assert\NotBlank(message="transfer.accountFrom.notBlank")
     */
    private $accountFromId;

    /**
     * @var Account
     * @JMS\SerializedName("accountTo")
     * @JMS\Type("AppBundle\Entity\Account")
     */
    private $accountTo;

    /**
     * @JMS\Type("integer")
     * @Assert\NotBlank(message="transfer.accountTo.notBlank")
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

    public function setAccountFromId($accountFromId)
    {
        $this->accountFromId = $accountFromId;

        // for JMS serialization
        $from = new Account();
        $from->setId($accountFromId);
        $this->setAccountFrom($from);
    }

    public function setAccountToId($accountToId)
    {
        $this->accountToId = $accountToId;

        // for JMS serialization
        $to = new Account();
        $to->setId($accountToId);
        $this->setAccountTo($to);
    }

    public function getAccountFromId()
    {
        return $this->accountFromId;
    }

    public function getAccountToId()
    {
        return $this->accountToId;
    }
}
