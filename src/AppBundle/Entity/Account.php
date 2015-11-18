<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

use Doctrine\ORM\QueryBuilder;

/**
 * Account
 *
 * @ORM\Table(name="account")
 * @ORM\Entity()
 */
class Account 
{
    /**
     * @var integer
     * @JMS\Groups({"transactions", "basic"})
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="account_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;
    
    
    /**
     * @var string
     * @JMS\Groups({"transactions", "basic"})
     * @ORM\Column(name="bank_name", type="string", length=100, nullable=true)
     */
    private $bank;

    /**
     * @var string
     * @JMS\Groups({"transactions", "basic"})
     * 
     * @ORM\Column(name="sort_code", type="string", length=6, nullable=true)
     */
    private $sortCode;

    /**
     * @var string
     * @JMS\Groups({"transactions", "basic"})
     * 
     * @ORM\Column(name="account_number", type="string", length=4, nullable=true)
     */
    private $accountNumber;

    /**
     * @var \DateTime
     * @JMS\Groups({"transactions", "basic"})
     * 
     * @ORM\Column(name="last_edit", type="datetime", nullable=true)
     */
    private $lastEdit;
    
    /**
     * @var \DateTime
     * @JMS\Groups({"transactions", "basic"})
     * 
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var string
     * @JMS\Groups({"transactions", "basic"})
     * 
     * @ORM\Column(name="opening_balance", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $openingBalance;
    
    /**
     * @var string
     * @JMS\Groups({"transactions", "basic"})
     * 
     * @ORM\Column(name="opening_date_explanation", type="text", nullable=true)
     */
    private $openingDateExplanation;

    /**
     * @var string
     * @JMS\Groups({"transactions", "basic"})
     * 
     * @ORM\Column(name="closing_balance", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $closingBalance;

    /**
     * @var string
     * @JMS\Groups({"transactions", "basic"})
     * 
     * @ORM\Column(name="closing_balance_explanation", type="text", nullable=true)
     */
    private $closingBalanceExplanation;
    
    /**
     * @var \Date
     * @JMS\Groups({"transactions", "basic"})
     * 
     * @ORM\Column(name="opening_date", type="date", nullable=true)
     */
    private $openingDate;

    /**
     * @var \Date
     * @JMS\Groups({"transactions", "basic"})
     * 
     * @ORM\Column(name="closing_date", type="date", nullable=true)
     */
    private $closingDate;

    /**
     * @var string
     * @JMS\Groups({"transactions", "basic"})
     * 
     * @ORM\Column(name="closing_date_explanation", type="text", nullable=true)
     */
    private $closingDateExplanation;
    
    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report", inversedBy="accounts")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
    private $report;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\AccountTransaction", mappedBy="account", cascade={"persist"})
     * @ORM\OrderBy({"id" = "ASC"})
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
     * @JMS\Groups({"transactions"})
     * @JMS\Accessor(getter="getMoneyInTotal")
     */
    private $moneyInTotal;
    
    /**
     * @JMS\Groups({"transactions"})
     * @JMS\Accessor(getter="getMoneyOutTotal")
     */
    private $moneyOutTotal;
    
    /**
     * @JMS\Groups({"transactions", "basic"})
     * @JMS\Accessor(getter="getMoneyTotal")
     */
    private $moneyTotal;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->lastEdit = null;
        $this->createdAt = new \DateTime();
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
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
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
     * @return string
     */
    public function getOpeningDateExplanation()
    {
        return $this->openingDateExplanation;
    }

    
    /**
     * @param string $openingDateExplanation
     */
    public function setOpeningDateExplanation($openingDateExplanation)
    {
        $this->openingDateExplanation = $openingDateExplanation;
        return $this;
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
     * @return string
     */
    public function getClosingBalanceExplanation()
    {
        return $this->closingBalanceExplanation;
    }

    /**
     * @param string $closingBalanceExplanation
     */
    public function setClosingBalanceExplanation($closingBalanceExplanation)
    {
        $this->closingBalanceExplanation = $closingBalanceExplanation;
        return $this;
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
     * @return string
     */
    public function getClosingDateExplanation()
    {
        return $this->closingDateExplanation;
    }

    /**
     * @param string $closingDateExplanation
     */
    public function setClosingDateExplanation($closingDateExplanation)
    {
        $this->closingDateExplanation = $closingDateExplanation;
        return $this;
    }

    /**
     * Set report
     *
     * @param Report $report
     * @return Account
     */
    public function setReport(Report $report = null)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report
     *
     * @return Report 
     */
    public function getReport()
    {
        return $this->report;
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
     * @param AccountTransaction $transaction
     */
    public function addTransaction(AccountTransaction $transaction)
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
        }
        
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
    
    /**
     * @param string $transactionTypeId
     * 
     * @return AccountTransaction
     */
    public function findTransactionByTypeId($transactionTypeId)
    {
        return $this->getTransactions()->filter(function($accountTransaction) use($transactionTypeId) {
            return $accountTransaction->getTransactionTypeId() == $transactionTypeId;
        })->first();
    }
    
    /**
     * @return float
     */
    public function getMoneyInTotal()
    {
        $ret = 0.0;
        foreach ($this->getMoneyIn() as $money) {
            $ret += $money->getAmount();
        }
        return $ret;
    }
    
    /**
     * @return float
     */
    public function getMoneyOutTotal()
    {
        $ret = 0.0;
        foreach ($this->getMoneyOut() as $money) {
            $ret += $money->getAmount();
        }
        return $ret;
    }
    
    /**
     * @return float
     */
    public function getMoneyTotal()
    {
        return $this->getOpeningBalance() + $this->getMoneyInTotal() - $this->getMoneyOutTotal();
    }

    /**
     * Remove transactions
     *
     * @param AccountTransaction $transactions
     */
    public function removeTransaction(AccountTransaction $transactions)
    {
        $this->transactions->removeElement($transactions);
    }
    
}
