<?php

namespace AppBundle\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="transaction", uniqueConstraints={@ORM\UniqueConstraint(name="report_unique_trans", columns={"report_id", "transaction_type_id"})})
 * @ORM\Entity
 */
class Transaction
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="transaction_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report\Report", inversedBy="transactions")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
    private $report;

    /**
     * @var TransactionType
     *
     * @JMS\Groups({"transaction", "transactionsIn", "transactionsOut"})
     * @JMS\Exclude
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report\TransactionType", fetch="EAGER")
     * @ORM\JoinColumn(name="transaction_type_id", referencedColumnName="id")
     */
    private $transactionType;

    /**
     * @var array
     *
     * @JMS\Type("array<string>")
     * @JMS\Groups({"transaction", "transactionsIn", "transactionsOut"})
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $amounts;

    /**
     * @var string
     * @JMS\Groups({"transaction", "transactionsIn", "transactionsOut"})
     *
     * @ORM\Column(name="more_details", type="text", nullable=true)
     */
    private $moreDetails;

    /**
     * @var string
     * @JMS\Groups({"transaction", "transactionsIn", "transactionsOut"})
     * @JMS\Type("boolean")
     * @JMS\Accessor(getter="hasMoreDetails")
     */
    private $hasMoreDetails;

    public function __construct(Report $report, TransactionType $transactionType, array $amounts)
    {
        $this->report = $report;
        $report->addTransaction($this);

        $this->transactionType = $transactionType;
        $this->amounts = $amounts;
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
     */
    public function setReport($report)
    {
        $this->report = $report;
    }

    /**
     * @return TransactionType
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"transaction", "transactionsIn", "transactionsOut"})
     * @JMS\SerializedName("id")
     *
     * @return string
     */
    public function getTransactionTypeId()
    {
        return $this->getTransactionType()->getId();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"transaction", "transactionsIn", "transactionsOut"})
     * @JMS\SerializedName("type")
     *
     * @return string
     */
    public function getTransactionClass()
    {
        return $this->getTransactionType() instanceof TransactionTypeIn ? 'in' : 'out';
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"transaction", "transactionsIn", "transactionsOut"})
     * @JMS\SerializedName("category")
     *
     * @return string
     */
    public function getCategoryString()
    {
        return $this->getTransactionType()->getCategory();
    }

    /**
     * @return array of floats
     * @JMS\VirtualProperty
     * @JMS\Groups({"transaction", "transactionsIn", "transactionsOut"})
     * @JMS\SerializedName("amounts_total")
     */
    public function getAmountsTotal()
    {
        return array_sum($this->getAmounts());
    }

    /**
     * @return string
     */
    public function getMoreDetails()
    {
        return $this->moreDetails;
    }

    /**
     * @return bool
     */
    public function hasMoreDetails()
    {
        return $this->getTransactionType()->getHasMoreDetails();
    }

    public function setTransactionType(TransactionType $transactionType)
    {
        $this->transactionType = $transactionType;

        return $this;
    }

    public function setMoreDetails($moreDetails)
    {
        $this->moreDetails = $moreDetails;

        return $this;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getAmounts()
    {
        return $this->amounts;
    }

    public function setAmounts($amounts)
    {
        $this->amounts = $amounts;

        return $this;
    }
}
