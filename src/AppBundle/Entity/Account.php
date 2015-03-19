<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Account
 *
 * @ORM\Table(name="account")
 * @ORM\Entity
 */
class Account
{
    /**
     * @var integer
     * @JMS\Groups({"transactions"})
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="account_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;
    
    
    /**
     * @var string
     * @JMS\Groups({"transactions"})
     * @ORM\Column(name="bank_name", type="string", length=100, nullable=true)
     */
    private $bank;

    /**
     * @var string
     *
     * @ORM\Column(name="sort_code", type="string", length=6, nullable=true)
     */
    private $sortCode;

    /**
     * @var string
     *
     * @ORM\Column(name="account_number", type="string", length=4, nullable=true)
     */
    private $accountNumber;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_edit", type="datetime", nullable=true)
     */
    private $lastEdit;

    /**
     * @var string
     *
     * @ORM\Column(name="opening_balance", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $openingBalance;

    /**
     * @var string
     *
     * @ORM\Column(name="closing_balance", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $closingBalance;

    /**
     * @var \Date
     *
     * @ORM\Column(name="opening_date", type="date", nullable=true)
     */
    private $openingDate;

    /**
     * @var \Date
     *
     * @ORM\Column(name="closing_date", type="date", nullable=true)
     */
    private $closingDate;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report", inversedBy="accounts")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
    private $report;

    /**
     * @var string
     *
     * @ORM\Column(name="date_justification", type="text", nullable=true)
     */
    private $dateJustification;

    /**
     * @var string
     *
     * @ORM\Column(name="balance_justification", type="text", nullable=true)
     */
    private $balanceJustification;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\AccountTransaction", mappedBy="account")
     */
    private $transactions;
    
    /**
     * @JMS\Groups({"transactions"})
     * @JMS\Accessor(getter="getMoneyIn")
     * @JMS\Type("array<AppBundle\Entity\AccountTransaction>") 
     */
    private $moneyIn;
    
    /**
     * @JMS\Groups({"transactions"})
     * @JMS\Accessor(getter="getMoneyOut")
     * @JMS\Type("array<AppBundle\Entity\AccountTransaction>") 
     */
    private $moneyOut;
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->transactions = new ArrayCollection();
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

    /**
     * Set bank
     *
     * @param string $bank
     * @return Account
     */
    public function setBank($bank)
    {
        $this->bank = $bank;

        return $this;
    }

    /**
     * Get bank
     *
     * @return string 
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * Set sortCode
     *
     * @param string $sortCode
     * @return Account
     */
    public function setSortCode($sortCode)
    {
        $this->sortCode = $sortCode;

        return $this;
    }

    /**
     * Get sortCode
     *
     * @return string 
     */
    public function getSortCode()
    {
        return $this->sortCode;
    }

    /**
     * Set accountNumber
     *
     * @param string $accountNumber
     * @return Account
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    /**
     * Get accountNumber
     *
     * @return string 
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * Set lastEdit
     *
     * @param \DateTime $lastEdit
     * @return Account
     */
    public function setLastEdit($lastEdit)
    {
        $this->lastEdit = $lastEdit;

        return $this;
    }

    /**
     * Get lastEdit
     *
     * @return \DateTime 
     */
    public function getLastEdit()
    {
        return $this->lastEdit;
    }

    /**
     * Set openingBalance
     *
     * @param string $openingBalance
     * @return Account
     */
    public function setOpeningBalance($openingBalance)
    {
        $this->openingBalance = $openingBalance;

        return $this;
    }

    /**
     * Get openingBalance
     *
     * @return string 
     */
    public function getOpeningBalance()
    {
        return $this->openingBalance;
    }

    /**
     * Set closingBalance
     *
     * @param string $closingBalance
     * @return Account
     */
    public function setClosingBalance($closingBalance)
    {
        $this->closingBalance = $closingBalance;

        return $this;
    }

    /**
     * Get closingBalance
     *
     * @return string 
     */
    public function getClosingBalance()
    {
        return $this->closingBalance;
    }

    /**
     * Set openingDate
     *
     * @param \DateTime $openingDate
     * @return Account
     */
    public function setOpeningDate($openingDate)
    {
        $this->openingDate = $openingDate;

        return $this;
    }

    /**
     * Get openingDate
     *
     * @return \DateTime 
     */
    public function getOpeningDate()
    {
        return $this->openingDate;
    }

    /**
     * Set closingDate
     *
     * @param \DateTime $closingDate
     * @return Account
     */
    public function setClosingDate($closingDate)
    {
        $this->closingDate = $closingDate;

        return $this;
    }

    /**
     * Get closingDate
     *
     * @return \DateTime 
     */
    public function getClosingDate()
    {
        return $this->closingDate;
    }

    /**
     * Set dateJustification
     *
     * @param string $dateJustification
     * @return Account
     */
    public function setDateJustification($dateJustification)
    {
        $this->dateJustification = $dateJustification;

        return $this;
    }

    /**
     * Get dateJustification
     *
     * @return string 
     */
    public function getDateJustification()
    {
        return $this->dateJustification;
    }

    /**
     * Set balanceJustification
     *
     * @param string $balanceJustification
     * @return Account
     */
    public function setBalanceJustification($balanceJustification)
    {
        $this->balanceJustification = $balanceJustification;

        return $this;
    }

    /**
     * Get balanceJustification
     *
     * @return string 
     */
    public function getBalanceJustification()
    {
        return $this->balanceJustification;
    }

    /**
     * Set report
     *
     * @param \AppBundle\Entity\Report $report
     * @return Account
     */
    public function setReport(\AppBundle\Entity\Report $report = null)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report
     *
     * @return \AppBundle\Entity\Report 
     */
    public function getReport()
    {
        return $this->report;
    }
    
    public function isDateJustifiable()
    {
        if(empty($this->openingDate) || empty($this->closingDate)){
            return true;
        }
        
        if(($this->openingDate == $this->report->getStartDate()) && ($this->closingDate == $this->report->getEndDate())){
            return true;
        } 
        return false;
    }
    
    /**
     * Checks if account balance is justifiable
     * 
     * @return boolean
     */
    public function isBalanceJustifiable()
    {
        $balanceOffset = $this->getBalanceOffset();
        $openingBalance = $this->openingBalance;
        $closingBalance = $this->closingBalance;
        
        if(isset($balanceOffset) && ($balanceOffset != 0) && isset($openingBalance) && isset($closingBalance)){
            return false;
        }
        return true;
    }
    
    /**
     * Get account balance offset
     */
    public function getBalanceOffset()
    {
        return ($this->closingBalance - $this->getCurrentBalance());
    }
    
    /**
     * Gets current account balance
     * @return integer $balance
     */
    public function getCurrentBalance()
    {
        $balance = 0;
        
        
        return $balance;
    }
    
    public function getTransactions()
    {
        return $this->transactions;
    }

    public function setTransactions($transactions)
    {
        $this->transactions = $transactions;
        return $this;
    }
    
    /**
     * @return AccountTransaction[]
     */
    public function getMoneyIn()
    {
        return $this->getTransactions()->filter(function(AccountTransaction $transaction) {
            return $transaction->getTransactionType() instanceof AccountTransactionTypeIn;
        });
    }

    /**
     * @return AccountTransaction[]
     */
    public function getMoneyOut()
    {
        return $this->getTransactions()->filter(function(AccountTransaction $transaction) {
            return $transaction->getTransactionType() instanceof AccountTransactionTypeOut;
        });
    }
}
