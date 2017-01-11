<?php

namespace AppBundle\Entity\Report;

use Doctrine\ORM\Mapping as ORM;

/**
 * @deprecated REMOVE WHEN OTPP is merged and migrated. don't remove earlier or the data gets deleted during migration
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report\TransactionType", fetch="EAGER")
     * @ORM\JoinColumn(name="transaction_type_id", referencedColumnName="id")
     */
    private $transactionType;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $amounts;

    /**
     * @var string
     *
     * @ORM\Column(name="more_details", type="text", nullable=true)
     */
    private $moreDetails;

    /**
     * @var string
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
     * @return string
     */
    public function getTransactionTypeId()
    {
        return $this->getTransactionType()->getId();
    }

    /**
     * @deprecated
     * @return string
     */
    public function getTransactionClass()
    {
        return $this->getTransactionType() instanceof TransactionTypeIn ? 'in' : 'out';
    }

    /**
     * @return string
     */
    public function getCategoryString()
    {
        return $this->getTransactionType()->getCategory();
    }

    /**
     * @return array of floats
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
