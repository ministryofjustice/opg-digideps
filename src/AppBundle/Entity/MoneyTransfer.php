<?php

namespace AppBundle\Entity;

use JMS\Serializer\Annotation as JMS;

/**
 * MoneyTransfer
 */
class MoneyTransfer
{

    /**
     * @JMS\Type("integer")
     * @var integer
     * @JMS\Groups({"transfers", "basic"})
     */
    private $id;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"transfers", "basic"})
     */
    private $amount;

    /**
     * @var Account
     * @JMS\Type("AppBundle\Entity\Account")
     * @JMS\Groups({"transfers", "basic"})
     */
    private $accountFrom;

    /**
     * @var Account
     * @JMS\Type("AppBundle\Entity\Account")
     * @JMS\Groups({"transfers", "basic"})
     */
    private $accountTo;

    
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set amount
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
     * Get amount
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
     * 
     * @param Account $from
     * @return MoneyTransfer
     */
    public function setAccountFrom(Account $from)
    {
        $this->accountFrom = $from;
        return $this;
    }


    /**
     * @param Account $to
     * @return MoneyTransfer
     */
    public function setAccountTo(Account $to)
    {
        $this->accountTo = $to;
        return $this;
    }

}