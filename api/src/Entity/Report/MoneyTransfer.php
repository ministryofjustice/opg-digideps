<?php

namespace AppBundle\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * MoneyTransfer.
 *
 * @ORM\Table(name="money_transfer")
 * @ORM\Entity
 */
class MoneyTransfer
{
    /**
     * @var int
     * @JMS\Groups({"money-transfer"})
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @JMS\Groups({"money-transfer"})
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $amount;

    /**
     * @var Account
     * @JMS\Groups({"account"})
     * @JMS\SerializedName("accountFrom")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report\BankAccount")
     * @ORM\JoinColumn(name="from_account_id", referencedColumnName="id")
     */
    private $from;

    /**
     * @var Account
     * @JMS\Groups({"account"})
     * @JMS\SerializedName("accountTo")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report\BankAccount")
     * @ORM\JoinColumn(name="to_account_id", referencedColumnName="id")
     */
    private $to;

    /**
     * @var Report
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report\Report", inversedBy="moneyTransfers")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

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
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return BankAccount
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param BankAccount $from
     *
     * @return MoneyTransfer
     */
    public function setFrom(BankAccount $from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @param BankAccount $to
     *
     * @return MoneyTransfer
     */
    public function setTo(BankAccount $to)
    {
        $this->to = $to;

        return $this;
    }

    public function getReport()
    {
        return $this->report;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"money-transfer"})
     * @JMS\Type("integer")
     * @JMS\SerializedName("reportId")
     */
    public function getReportId()
    {
        return $this->getReport()->getId();
    }

    public function setReport(Report $report)
    {
        $this->report = $report;

        return $this;
    }
}
