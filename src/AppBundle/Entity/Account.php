<?php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @Assert\Callback(methods={"isOpeningDateValidOrExplanationIsGiven"}, groups={"opening_balance"})
 * @Assert\Callback(methods={"isClosingDateValidOrExplanationIsGiven"}, groups={"closing_balance"})
 */
class Account
{
    use Traits\HasReportTrait;
    
    const OPENING_DATE_SAME_YES = 'yes';
    const OPENING_DATE_SAME_NO = 'no';
    
    /**
     * Keep in sync with api
     */
    public static $types = [
        'current' => 'Current account',
        'savings' => 'Savings account',
        'isa' => 'ISA',
        'postoffice' => 'Post office account',
        'cfo' => 'Court funds office account',
        'other' => 'Other'
    ];
    
    /**
     * @JMS\Type("integer")
     * @var integer $id
     */
    private $id;
    
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="account.bank.notBlank", groups={"basic", "bank_name"})
     * @Assert\Length(max=100, min=2,  minMessage= "account.bank.minMessage", maxMessage= "account.bank.maxMessage", groups={"basic", "bank_name"})
     * 
     * @JMS\Groups({"edit_details", "edit_details_report_due", "add", "add_edit"})
     * 
     * @var string $bank
     */
    private $bank;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="account.accountType.notBlank", groups={"basic", "add_edit"})
     * @Assert\Length(max=100, maxMessage= "account.accountType.maxMessage", groups={"basic", "add_edit"})
     *
     * @JMS\Groups({"edit_details", "edit_details_report_due", "add", "add_edit"})
     *
     * @var string $accountType
     */
    private $accountType;
    
    /**
     * @JMS\Type("string")
     *
     * @var string $accountType
     */
    private $accountTypeText;
    
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank( message="account.sortCode.notBlank", groups={"basic", "sortcode"})
     * @Assert\Type(type="numeric", message="account.sortCode.type", groups={"basic", "sortcode"})
     * @Assert\Length(min=6, max=6, exactMessage = "account.sortCode.length", groups={"basic", "sortcode"})
     * @JMS\Groups({"edit_details", "edit_details_report_due", "add", "add_edit"})
     * 
     * @var string $sortCode
     */
    private $sortCode;
    
    /**
     *
     * @JMS\Type("string")
     * @Assert\NotBlank(message="account.accountNumber.notBlank", groups={"basic", "add_edit"})
     * @Assert\Type(type="numeric", message="account.accountNumber.type", groups={"basic", "add_edit"})
     * @Assert\Length(exactMessage="account.accountNumber.length",min=4, max=4, groups={"basic", "add_edit"})
     * @JMS\Groups({"edit_details", "edit_details_report_due", "add", "add_edit"})
     * 
     * @var string $accountNumber
     */
    private $accountNumber;
    
    /**
     * @JMS\Type("DateTime")
     * @Assert\NotBlank(message="account.openingDate.notBlank", groups={"opening_balance"})
     * @Assert\Date(message="account.openingDate.date", groups={"opening_balance"})
     * @JMS\Groups({"edit_details", "edit_details_report_due", "add", "add_edit"})
     * 
     * @var \DateTime 
     */
    private $openingDate;
    
    /**
     * @deprecated since accounts_mk2
     * @Assert\NotBlank(message="account.openingDateSameAsReportDate.notBlank", groups={"checkbox_matches_date"})
     * 
     * @var string OPENING_DATE_SAME_* values
     */
    private $openingDateMatchesReportDate;
      
    /**
     * @deprecated since accounts_mk2
     * @JMS\Type("string")
     * @JMS\Groups({"transactions", "basic", "edit_details", "add","edit_details_report_due"})
     */
    private $openingDateExplanation;
    
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"edit_details", "edit_details_report_due", "add", "add_edit"})
     *
     * @Assert\NotBlank(message="account.openingBalance.notBlank", groups={"basic", "add_edit"})
     * @Assert\Type(type="numeric", message="account.openingBalance.type", groups={"basic", "add_edit"})
     * @Assert\Range(max=10000000000, maxMessage = "account.openingBalance.outOfRange", groups={"basic", "add_edit"})
     *
     * @var decimal
     */
    private $openingBalance;
  
    
    /**
     * @JMS\Type("string")
     * @Assert\Type(type="numeric", message="account.closingBalance.type", groups={"closing_balance", "add_edit"})
     * @Assert\Range(max=10000000000, maxMessage = "account.closingBalance.outOfRange", groups={"closing_balance", "add_edit"})
     * @JMS\Groups({"balance", "edit_details_report_due","edit_details", "add_edit"})
     * 
     * @var decimal
     */
    private $closingBalance;
    
    /**
     * @deprecated since accounts_mk2
     * @JMS\Type("string")
     * @JMS\Groups({"basic", "balance", "edit_details_report_due"})
     */
    private $closingBalanceExplanation;
    
    /**
     * @deprecated since accounts_mk2
     * @JMS\Type("DateTime")
     * @Assert\NotBlank(message="account.closingDate.notBlank", groups={"closing_balance"})
     * @Assert\Date(message="account.closingDate.date", groups={"closing_balance"})
     * @JMS\Groups({"balance", "edit_details_report_due"})
     * @var \DateTime  
     */
    private $closingDate;
    
    /**
     * @deprecated since accounts_mk2
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
        
        return $this->report->getStartDate()->format('Y-m-d') === $this->getOpeningDate()->format('Y-m-d');
    }
    
    /**
     * @return boolean
     */
    public function isClosingDateEqualToReportEndDate()
    {
        if (!$this->getClosingDate()) {
            return false;
        }
        
        return $this->report->getEndDate()->format('Y-m-d') === $this->getClosingDate()->format('Y-m-d');
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
     * @return string
     */
    public function getOpeningDateMatchesReportDate()
    {
        return $this->openingDateMatchesReportDate;
    }
    
    /**
     * @param string $openingDateMatchesReportDate
     */
    public function setOpeningDateMatchesReportDate($openingDateMatchesReportDate)
    {
        $this->openingDateMatchesReportDate = $openingDateMatchesReportDate;
    }

    /**
     * @return string
     */
    public function getAccountType()
    {
        return $this->accountType;
    }
    
    public function getAccountTypeText()
    {
        return $this->accountTypeText;
    }
    
     
    /**
     * Sort code required
     * @return string
     */
    public function requiresBankNameAndSortCode()
    {
        return !in_array($this->getAccountType(), ['postoffice', 'cfo']);
    }

    /**
     * @param string $accountType
     */
    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;
    }
    
    
}
