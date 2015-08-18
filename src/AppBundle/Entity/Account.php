<?php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @Assert\Callback(methods={"isOpeningDateValidOrExplanationIsGiven"}, groups={"basic"})
 * @Assert\Callback(methods={"isClosingDateValidOrExplanationIsGiven"}, groups={"closing_balance"})
 * @Assert\Callback(methods={"isClosingBalanceMatchingTransactionsSum"}, groups={"closing_balance"})
 */
class Account
{
    const OPENING_DATE_SAME_YES = 'yes';
    const OPENING_DATE_SAME_NO = 'no';
    /**
     * @JMS\Type("integer")
     * @var integer $id
     */
    private $id;
    
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="account.bank.notBlank", groups={"basic"})
     * @Assert\Length(max=100, maxMessage= "account.bank.maxMessage", groups={"basic"})
     * 
     * @JMS\Groups({"edit_details", "edit_details_report_due", "add"})
     * 
     * @var string $bank
     */
    private $bank;
    
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank( message="account.sortCode.notBlank", groups={"basic"})
     * @Assert\Type(type="numeric", message="account.sortCode.type", groups={"basic"})
     * @Assert\Length(min=6, minMessage = "account.sortCode.length", groups={"basic"})
     * @JMS\Groups({"edit_details", "edit_details_report_due", "add"})
     * 
     * @var string $sortCode
     */
    private $sortCode;
    
    /**
     *
     * @JMS\Type("string")
     * @Assert\NotBlank(message="account.accountNumber.notBlank", groups={"basic"})
     * @Assert\Type(type="numeric", message="account.accountNumber.type", groups={"basic"})
     * @Assert\Length(minMessage="account.accountNumber.length",min=4, groups={"basic"})
     * @JMS\Groups({"edit_details", "edit_details_report_due", "add"})
     * 
     * @var string $accountNumber
     */
    private $accountNumber;
    
    /**
     * field not mapped in the API.
     * Only used for JS version of the account form, to make easier holding options with form interactions
     * 
     * @var string 
     */
    private $openingDateSame;
    
    /**
     * @JMS\Type("DateTime")
     * @Assert\NotBlank(message="account.openingDate.notBlank", groups={"basic"})
     * @Assert\Date(message="account.openingDate.date", groups={"basic"})
     * @JMS\Groups({"edit_details", "edit_details_report_due", "add"})
     * 
     * @var \DateTime 
     */
    private $openingDate;
    
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"edit_details", "edit_details_report_due", "add"})
     * @Assert\NotBlank(message="account.openingBalance.notBlank", groups={"basic"})
     * @Assert\Type(type="numeric", message="account.openingBalance.type", groups={"basic"})
     * @Assert\Range(max=10000000000, maxMessage = "account.openingBalance.outOfRange", groups={"basic"})
     * 
     * @var decimal
     */
    private $openingBalance;
    
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"transactions", "basic", "edit_details", "add","edit_details_report_due"})
     */
    private $openingDateExplanation;
    
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="account.closingBalance.notBlank", groups={"closing_balance"})
     * @Assert\Type(type="numeric", message="account.closingBalance.type", groups={"closing_balance"})
     * @Assert\Range(max=10000000000, maxMessage = "account.closingBalance.outOfRange", groups={"closing_balance"})
     * @JMS\Groups({"balance", "edit_details_report_due","edit_details"})
     * 
     * @var decimal
     */
    private $closingBalance;
    
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"basic", "balance", "edit_details_report_due"})
     */
    private $closingBalanceExplanation;
    
    /**
     * @JMS\Type("DateTime")
     * @Assert\NotBlank(message="account.closingDate.notBlank", groups={"closing_balance"})
     * @Assert\Date(message="account.closingDate.date", groups={"closing_balance"})
     * @JMS\Groups({"balance", "edit_details_report_due"})
     * @var \DateTime  
     */
    private $closingDate;
    
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"basic", "balance", "edit_details_report_due"})
     */
    private $closingDateExplanation;
    
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
     * @JMS\Groups({"add"})
     */
    private $report;
    
    /**
     * @var Report
     */
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
    
    public function getOpeningDateExplanation()
    {
        return $this->openingDateExplanation;
    }

    public function setOpeningDateExplanation($openingDateExplanation)
    {
        $this->openingDateExplanation = $openingDateExplanation;
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
    }

    /**
     * @return boolean
     */
    public function hasClosingBalance()
    {
        if(is_null($this->closingBalance)){
            return false;
        }
        return true;
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
    
    public function setReportObject(Report $reportObject = null)
    {
        $this->reportObject = $reportObject;
        return $this;
    }
    
    /**
     * @return Report
     */
    public function getReportObject($validate = false)
    {
        if ($validate && !$this->reportObject instanceof Report) {
            throw new \RuntimeException("Report object not found.");
        }
        
        return $this->reportObject;
    }
    
    /**
     * Add violation if Opening date is not the same as the report start date and there is not explanation
     */
    public function isOpeningDateValidOrExplanationIsGiven(ExecutionContextInterface $context)
    {
        // trigger error in case of date mismatch (report start date different from account opening date) and explanation is empty
        if (!$this->isOpeningDateValid() && !$this->getOpeningDateExplanation()) {
            $context->addViolationAt('openingDate', 'account.openingDate.notSameAsReport');
            $context->addViolationAt('openingDateExplanation', 'account.openingDateExplanation.notBlankOnDateMismatch');
        }
    }
    
    /**
     * Add violation if closing date is not the same as the report end date and there is not explanation
     */
    public function isClosingDateValidOrExplanationIsGiven(ExecutionContextInterface $context)
    {
        // trigger error in case of date mismatch (report end date different from account closing date) and explanation is empty
        if ($this->getClosingDate() !== null && !$this->isClosingDateValid()) {
            $context->addViolationAt('closingDate', 'account.closingDate.mismatch');
            $context->addViolationAt('closingDateExplanation', 'account.closingDateExplanation.notBlankOnDateMismatch');
        }
    }
    
    /**
     * Add violation if closing balance does not match sum of transactions
     */
    public function isClosingBalanceMatchingTransactionsSum(ExecutionContextInterface $context)
    {
        // trigger error in case of date mismatch (report end date different from account closing date) and explanation is empty
        if ($this->getClosingBalance() !== null && !$this->isClosingBalanceValid()) {
            $context->addViolationAt('closingBalance', 'account.closingBalance.mismatch', [
                '%moneyTotal%' => $this->getMoneyTotal()
            ]);
            $context->addViolationAt('closingBalanceExplanation', 'account.closingBalanceExplanation.notBlankOnDateMismatch');
        }
    }
    
    
    /**
     * @return boolean
     */
    public function isOpeningDateValid()
    {
        if (!$this->getOpeningDate()) {
            return false;
        }
        if (!$this->reportObject) {
            // 'reportObject' needs refactor, 'report' should be the object and not the id, so that more manageable by JMS
            error_log(__METHOD__ . ' : account reportObject not available', E_WARNING);
            return false;
        }
        return $this->reportObject->getStartDate()->format('Y-m-d') === $this->getOpeningDate()->format('Y-m-d');
    }
    
    /**
     * @return boolean
     */
    public function isClosingDateEqualToReportEndDate()
    {
        if (!$this->getClosingDate()) {
            return false;
        }
        if (!$this->reportObject) {
            // 'reportObject' needs refactor, 'report' should be the object and not the id, so that more manageable by JMS
            error_log(__METHOD__ . ' : account reportObject not available', E_WARNING);
            return false;
        }
        return $this->reportObject->getEndDate()->format('Y-m-d') === $this->getClosingDate()->format('Y-m-d');
    }
    
    /**
     * @return boolean
     */
    public function isClosingBalanceMatchingTransactionSum()
    {
        return $this->getClosingBalance() == $this->getMoneyTotal();
    }

    /**
     * @return boolean
     */
    public function isClosingDateValid()
    {
        return $this->isClosingDateEqualToReportEndDate() || $this->getClosingDateExplanation();
    }
    
    
    /**
     * @return boolean
     */
    public function isClosingBalanceValid()
    {
        return $this->isClosingBalanceMatchingTransactionSum() || $this->getClosingBalanceExplanation();
    }
    
    /**
     * @return boolean
     */
    public function isClosingBalanceAndDateValid()
    {
        return $this->isClosingDateValid() && $this->isClosingBalanceValid();
    }
    
    /**
     * @return boolean
     */
    public function needsClosingBalanceData()
    {
        return $this->getClosingDate() == null || !$this->isClosingBalanceAndDateValid();
    }
    
    /**
     * @return true
     */
    public function hasAtLeastOneTotalOutAndIn()
    {
        $moneyInTotalTranactions = array_filter($this->getMoneyIn(), function (AccountTransaction $t) {
           return $t->getAmount() !== null;
        });
       
        $moneyOutTotalTranactions = array_filter($this->getMoneyOut(), function (AccountTransaction $t) {
           return $t->getAmount() !== null;
        });

        return count($moneyInTotalTranactions) > 0 && count($moneyOutTotalTranactions) > 0;
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
    
    /**
     * Virtual field.
     * 
     * @return string 'yes' if opening date is the same as reprot start date, "no" otherwise
     * @throws \RuntimeException
     */
    public function getOpeningDateSame()
    {
        return $this->getOpeningDate();
    }

    
    public function setOpeningDateSame($openingDateSame)
    {
        $this->openingDateSame = $openingDateSame;
    }
}