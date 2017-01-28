<?php

namespace AppBundle\Entity\Report;

use JMS\Serializer\Annotation as JMS;

class MoneyTransactionShort
{
    /**
     * @var int
     *
     * @JMS\Groups({"moneyTransactionShort"})
     */
    private $id;

    /**
     * @var Report
     */
    private $report;

    /**
     * @var float
     *
     * @JMS\Type("string")
     * @JMS\Groups({"moneyTransactionShort"})
     */
    private $amount;

    /**
     * @var string
     *
     * @JMS\Groups({"moneyTransactionShort"})
     */
    private $description;

    /**
     * @var DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"moneyTransactionShort"})
     */
    private $date;

    /**
     * Discriminator field
     *
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"moneyTransactionShort"})
     */
    private $type;

    /**
     * MoneyTransactionShort constructor.
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return MoneyTransactionShort
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param Report $report
     *
     * @return MoneyTransactionShort
     */
    public function setReport($report)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     *
     * @return MoneyTransactionShort
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return MoneyTransactionShort
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     *
     * @return MoneyTransactionShort
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
