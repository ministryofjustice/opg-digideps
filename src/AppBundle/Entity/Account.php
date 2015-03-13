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
     * @Assert\Length(max = 6,min = 6, minMessage = "account.sortCode.length", maxMessage = "account.sortCode.length")
     * 
     * @var string $sortCode
     */
    private $sortCode;
    
    /**
     *
     * @JMS\Type("string")
     * @Assert\NotBlank(message="account.accountNumber.notBlank")
     * @Assert\Type(type="numeric", message="account.accountNumber.type")
     * @Assert\Length(minMessage="account.accountNumber.length",maxMessage="account.accountNumber.length", max=4,min=4)
     * 
     * @var string $accountNumber
     */
    private $accountNumber;
    
    /**
     * @JMS\Type("DateTime")
     * @Assert\NotBlank(message="account.openingDate.notBlank")
     * @Assert\Date(message="account.openingDate.date")
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
     * @JMS\Type("DateTime")
     * @var type 
     */
    private $lastEdit;
    
    /**
     * @JMS\Type("integer")
     */
    private $report;
    
    private $reportObject;
    
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
    
    public function setOpeningDate($openingDate)
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
    
    public function setLastEdit($lastEdit)
    {
        $this->lastEdit = $lastEdit;
        return $this;
    }
    
    public function getLastEdit()
    {
        return $this->lastEdit;
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
    
    public function isValidOpeningDate(ExecutionContextInterface $context)
    {
        $reportStartDate = $this->reportObject->getStartDate();
        $reportEndDate = $this->reportObject->getEndDate();
        
        if(($reportStartDate > $this->openingDate) || ($reportEndDate < $this->openingDate)){
             $context->addViolationAt('openingDate','Opening balance date must be between '.$reportStartDate->format('d/m/Y').' and '.$reportEndDate->format('d/m/Y'));
        }
    }
    
}