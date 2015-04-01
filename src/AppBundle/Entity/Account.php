<?php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @Assert\Callback(methods={"isValidOpeningDate"})
 */
class Account
{
    /**
     * @JMS\Type("integer")
     * @var integer $id
     */
    private $id;
    
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="account.bank.notBlank")
     * @Assert\Type(type="string", message="account.bank.type")
     * 
     * @var string $bank
     */
    private $bank;
    
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank( message="account.sortCode.notBlank")
     * @Assert\Type(type="numeric", message="account.sortCode.type")
     * @Assert\Length(min=6, minMessage = "account.sortCode.length")
     * 
     * @var string $sortCode
     */
    private $sortCode;
    
    /**
     *
     * @JMS\Type("string")
     * @Assert\NotBlank(message="account.accountNumber.notBlank")
     * @Assert\Type(type="numeric", message="account.accountNumber.type")
     * @Assert\Length(minMessage="account.accountNumber.length",min=4)
     * 
     * @var string $accountNumber
     */
    private $accountNumber;
    
    /**
     * @JMS\Type("DateTime")
     * @Assert\NotBlank(message="account.openingDate.notBlank")
     * @Assert\Date(message="account.openingDate.date")
     *
     * @var type 
     */
    private $openingDate;
    
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="account.openingBalance.notBlank")
     * @Assert\Type(type="numeric", message="account.openingBalance.type")
     *
     * @var decimal
     */
    private $openingBalance;
    
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="account.closingBalance.notBlank", groups={"balance"})
     * @Assert\Type(type="numeric", message="account.closingBalance.type", groups={"balance"})
     * @JMS\Groups({"balance"})
     * 
     * @var decimal
     */
    private $closingBalance;
    
    /**
     * @JMS\Type("DateTime")
     * @JMS\Groups({"balance"})
     * @Assert\NotBlank(message="account.closingDate.notBlank", groups={"balance"})
     * @Assert\Date(message="account.closingDate.date", groups={"balance"})
     * @var \DateTime  
     */
    private $closingDate;
    
    /**
     * @JMS\Type("DateTime")
     * @var \DateTime 
     */
    private $lastEdit;
    
     /**
     * @JMS\Type("DateTime")
     * @var \DateTime 
     */
    private $createdAt;
    
    /**
     * @JMS\Type("integer")
     */
    private $report;
    
    private $reportObject;
    
    /**
     * @JMS\Type("array<AppBundle\Entity\AccountTransaction>") 
     * @JMS\Groups({"transactions"})
     */
    private $moneyIn;
    
    /**
     * @JMS\Type("array<AppBundle\Entity\AccountTransaction>")
     * @JMS\Groups({"transactions"}) 
     */
    private $moneyOut;
    
    /**
     * @JMS\Type("double")
     * @JMS\Groups({"transactions"})
     */
    private $moneyInTotal;
    
    /**
     * @JMS\Type("double")
     * @JMS\Groups({"transactions"})
     */
    private $moneyOutTotal;
    
    /**
     * @JMS\Type("double")
     * @JMS\Groups({"transactions"})
     */
    private $moneyTotal;
    
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    public function setBank($bank)
    {
        $this->bank = $bank;
        return $this;
    }
    
    public function getBank()
    {
        return $this->bank;
    }
    
    public function setSortCode($sortCode)
    {
        $this->sortCode = $sortCode;
        return $this;
    }
    
    public function getSortCode()
    {
        return $this->sortCode;
    }
    
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }
    
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }
    
    public function setOpeningDate(\DateTime $openingDate = null)
    {
        $this->openingDate = $openingDate;
    }
    
    public function getOpeningDate()
    {
        return $this->openingDate;
    }
    
    public function setOpeningBalance($openingBalance)
    {
        $this->openingBalance = $openingBalance;
        return $this;
    }
    
    public function getOpeningBalance()
    {
        return $this->openingBalance;
    }
    
    /**
     * @param type $closingBalance
     * @return type
     */
    public function setClosingBalance($closingBalance)
    {
        $this->closingBalance = $closingBalance;
        return $this->closingBalance;
    }
    
    /**
     * @return decimal $closingBalance
     */
    public function getClosingBalance()
    {
        return $this->closingBalance;
    }
    
    public function setLastEdit($lastEdit)
    {
        $this->lastEdit = $lastEdit;
        return $this;
    }
    
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
     * @return \DateTime
     */
    public function getClosingDate()
    {
        return $this->closingDate;
    }

    public function setClosingDate($closingDate)
    {
        $this->closingDate = $closingDate;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }
        
    public function getReport()
    {
        return $this->report;
    }
    
    
    public function setReport($report)
    {
        $this->report = $report;
        return $this;
    }
    
    public function setReportObject($reportObject)
    {
        $this->reportObject = $reportObject;
        return $this;
    }
    
    public function getReportObject()
    {
        return $this->reportObject;
    }
    
    /**
     * Add violation if Opening date is not between report start and end date
     */
    public function isValidOpeningDate(ExecutionContextInterface $context)
    {
        $reportStartDate = clone $this->reportObject->getStartDate();
        $reportEndDate = clone $this->reportObject->getEndDate();
        
        $reportStartDate->setTime(0, 0, 0);
        $reportEndDate->setTime(23, 59, 59);
        
        if(($reportStartDate > $this->openingDate) || ($reportEndDate < $this->openingDate)){
             $context->addViolationAt('openingDate','Opening balance date must be between '.$reportStartDate->format('d/m/Y').' and '.$reportEndDate->format('d/m/Y'));
        }
    }
    
    public function getMoneyIn()
    {
        return $this->moneyIn;
    }

    public function getMoneyOut()
    {
        return $this->moneyOut;
    }

    public function setMoneyIn(array $moneyIn)
    {
        $this->moneyIn = $moneyIn;
    }

    public function setMoneyOut(array $moneyOut)
    {
        $this->moneyOut = $moneyOut;
    }
    
    /**
     * @return float
     */
    public function getMoneyInTotal()
    {
        return $this->moneyInTotal;
    }

    /**
     * @return float
     */
    public function getMoneyOutTotal()
    {
        return $this->moneyOutTotal;
    }

    /**
     * @return float
     */
    public function getMoneyTotal()
    {
        return $this->moneyTotal;
    }

}