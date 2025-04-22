<?php

namespace App\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class MoneyTransactionShort
{
    /**
     * @var int
     * @JMS\Type("integer")
     *
     * @JMS\Groups({"moneyTransactionShort"})
     */
    private $id;

    /**
     * @JMS\Type("App\Entity\Report\Report")
     *
     * @var Report
     */
    private $report;

    /**
     * @var float
     *
     * @JMS\Type("string")
     * @JMS\Groups({"moneyTransactionShort"})
     *
     * @Assert\NotBlank(message="moneyTransactionShort.amount.notBlank", groups={"money-transaction-short"})
     * @Assert\Type(type="numeric", message="moneyTransactionShort.amount.type", groups={"money-transaction-short"})
     * @Assert\Range(min=1000, max=10000000, notInRangeMessage = "moneyTransactionShort.amount.notInRangeMessage", groups={"money-transaction-short"})
     */
    private $amount;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"moneyTransactionShort"})
     *
     * @Assert\NotBlank(message="moneyTransactionShort.description.notBlank", groups={"money-transaction-short"})
     */
    private $description;

    /**
     * @var DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"moneyTransactionShort"})
     *
     * @Assert\Type(type="DateTimeInterface", message="moneyTransactionShort.date.notValid", groups={"money-transaction-short"})
     */
    private $date;

    /**
     * Discriminator field.
     *
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"moneyTransactionShort"})
     */
    private $type;

    /**
     * MoneyTransactionShort constructor.
     *
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
