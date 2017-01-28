<?php

namespace AppBundle\Entity\Report;

use JMS\Serializer\Annotation as JMS;

class MoneyTransactionShort
{
    /**
     * @var int
     *
     * @JMS\Groups({"transactionsShortIn", "transactionsShortOut"})
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
     * @JMS\Groups({"transactionsShortIn", "transactionsShortOut"})
     */
    private $amount;

    /**
     * @var string
     *
     * @JMS\Groups({"transactionsShortIn", "transactionsShortOut"})
     */
    private $description;

    /**
     * @var DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    private $date;

    /**
     * Discriminator field
     *
     * @var string
     * @JMS\Exclude
     */
    private $type;

    /**
     * MoneyTransactionShort constructor.
     *
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
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
