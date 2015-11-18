<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;


/**
 * @ORM\Table(name="transaction", uniqueConstraints={@ORM\UniqueConstraint(name="report_unique_trans", columns={"report_id", "transaction_type_id"})})
 * @ORM\Entity
 */
class Transaction
{
    /**
     * @JMS\Groups({"transactions"})
     * @var integer
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report", inversedBy="transactions")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
    private $report;

    /**
     * @var TransactionType
     * 
     * @JMS\Groups({"transactions"})
     * @JMS\Type("string") 
     * @JMS\SerializedName("type")
     * @JMS\Accessor(getter="getTransactionTypeId")
     * 
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TransactionType")
     * @ORM\JoinColumn(name="transaction_type_id", referencedColumnName="id")
     */
    private $transactionType;
    
    /**
     * @var string
     * @JMS\Groups({"transactions"})
     *
     * @ORM\Column(type="decimal", precision=14, scale=2, nullable=true)
     */
    private $amount;
    
    /**
     * @var string
     * @JMS\Groups({"transactions"})
     *
     * @ORM\Column(name="more_details", type="text", nullable=true)
     */
    private $moreDetails;
    
    /**
     * @var string
     * @JMS\Groups({"transactions"})
     * @JMS\Type("boolean")
     * @JMS\Accessor(getter="hasMoreDetails")
     */
    private $hasMoreDetails;
    
    
    public function __construct(Report $report, TransactionType $transactionType, $amount)
    {
        $this->report = $report;
        
        $this->transactionType = $transactionType;
        $this->amount = $amount;
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
     * @return string
     */
    public function getTransactionCategory()
    {
        return $this->getTransactionType()->getCategory();
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getMoreDetails()
    {
        return $this->moreDetails;
    }

    /**
     * @return boolean
     */
    public function hasMoreDetails()
    {
        return $this->getTransactionType()->getHasMoreDetails();
    }

    public function setTransactionType(AccountTransactionType $transactionType)
    {
        $this->transactionType = $transactionType;
        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        
        return $this;
    }

    public function setMoreDetails($moreDetails)
    {
        $this->moreDetails = $moreDetails;
        
        return $this;
    }


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
}
