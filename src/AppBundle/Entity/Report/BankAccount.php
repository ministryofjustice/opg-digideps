<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Report\Traits\HasReportTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class BankAccount
{
    use HasReportTrait;

    const OPENING_DATE_SAME_YES = 'yes';
    const OPENING_DATE_SAME_NO = 'no';

    /**
     * Keep in sync with api.
     */
    public static $types = [
        'current' => 'Current account',
        'savings' => 'Savings account',
        'isa' => 'ISA',
        'postoffice' => 'Post Office account',
        'cfo' => 'Court Funds Office account',
        'other' => 'Other type of account',
        'other_no_sortcode' => 'Other type of account without sort code',
    ];

    private static $typesNotRequiringSortCode = [
        'postoffice',
        'cfo',
        'other_no_sortcode'
    ];

    private static $typesNotRequiringBankName = [
        'postoffice',
        'cfo'
    ];

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="account.accountType.notBlank", groups={"bank-account-type"})
     * @Assert\Length(max=100, maxMessage= "account.accountType.maxMessage", groups={"bank-account-type"})
     *
     * @JMS\Groups({"account"})
     *
     * @var string
     */
    private $accountType;


    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="account.bank.notBlank", groups={"bank-account-name"})
     * @Assert\Length(max=500, min=2,  minMessage= "account.bank.minMessage", maxMessage= "account.bank.maxMessage", groups={"bank-account-name"})
     *
     * @JMS\Groups({"account"})
     *
     * @var string
     */
    private $bank;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $accountTypeText;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="account.accountNumber.notBlank", groups={"bank-account-number"})
     * @Assert\Type(type="alnum", message="account.accountNumber.type", groups={"bank-account-number"})
     * @Assert\Length(exactMessage="account.accountNumber.length",min=4, max=4, groups={"bank-account-number"})
     * @JMS\Groups({"account"})
     *
     * @var string
     */
    private $accountNumber;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"account"})
     *
     * @var string
     */
    private $sortCode;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"account"})
     *
     * @Assert\NotBlank(message="account.openingBalance.notBlank", groups={"bank-account-opening-balance"})
     * @Assert\Type(type="numeric", message="account.openingBalance.type", groups={"bank-account-opening-balance"})
     * @Assert\Range(max=1000000000, maxMessage = "account.openingBalance.outOfRange", groups={"bank-account-opening-balance"})
     *
     * @var decimal
     */
    private $openingBalance;

    /**
     * @JMS\Type("string")
     * @Assert\Type(type="numeric", message="account.closingBalance.type", groups={"bank-account-closing-balance"})
     * @Assert\Range(max=1000000000, maxMessage = "account.closingBalance.outOfRange", groups={"bank-account-closing-balance"})
     * @JMS\Groups({"account"})
     *
     * @var decimal
     */
    private $closingBalance;

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"account"})
     * @Assert\NotBlank(message="account.isClosed.notBlank", groups={"bank-account-is-closed"})
     *
     * @var bool
     */
    private $isClosed;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"account"})
     * @Assert\NotBlank(message="account.isJointAccount.notBlank", groups={"bank-account-is-joint"})
     *
     * @var string
     */
    private $isJointAccount;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $lastEdit;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"account"})
     *
     * @var string
     */
    private $meta;

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

    /**
     * Get bank account name in one line
     * <bank> - <type> (****<last 4 digits>)
     * e.g.
     * barclays - Current account (****1234)
     * Natwest - ISA (****4444)
     * @return string
     */
    public function getNameOneLine()
    {
         return (!empty($this->getBank()) ? $this->getBank() . ' - '  : '')
             . $this->getAccountTypeText()
             . ' (****' . $this->getAccountNumber() . ')';
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
     * @param float $closingBalance
     *
     * @return self
     */
    public function setClosingBalance($closingBalance)
    {
        $this->closingBalance = $closingBalance;

        if (!$this->isClosingBalanceZero()) {
            $this->setIsClosed(false);
        }

        return $this;
    }

    /**
     * @return decimal $closingBalance
     */
    public function getClosingBalance()
    {
        return $this->closingBalance;
    }

    public function isClosingBalanceZero()
    {
        return $this->closingBalance !== null && round($this->closingBalance, 2) === 0.00;
    }

    /**
     * @return bool
     */
    public function hasClosingBalance()
    {
        if (is_null($this->closingBalance)) {
            return false;
        }

        return true;
    }

    public function getIsClosed()
    {
        return $this->isClosed;
    }

    public function setIsClosed($isClosed)
    {
        $this->isClosed = $isClosed;

        return $this;
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
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
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
     * Sort code required.
     *
     * @return string
     */
    public function requiresSortCode()
    {
        return !in_array($this->getAccountType(), self::$typesNotRequiringSortCode);
    }

    /**
     * Bank name required.
     *
     * @return string
     */
    public function requiresBankName()
    {
        return !in_array($this->getAccountType(), self::$typesNotRequiringBankName);
    }

    /**
     * @param string $accountType
     */
    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;
    }

    public function getIsJointAccount()
    {
        return $this->isJointAccount;
    }

    public function setIsJointAccount($isJointAccount)
    {
        $this->isJointAccount = $isJointAccount;

        return $this;
    }

    public function getMeta()
    {
        return $this->meta;
    }

    public function setMeta($meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * Format the account name for CSV.
     *
     * @return string
     */
    public function getDisplayName()
    {
        switch($this->getAccountType()) {
            case "current":
                return ($this->getIsJointAccount() ? 'Joint current ': 'Current') . ' account (****' . $this->getAccountNumber() . ' / ' . $this->getDisplaySortCode() . ')';
            case "savings":
                return ($this->getIsJointAccount() ? 'Joint savings ': 'Savings') . ' account (****' . $this->getAccountNumber() . ' / ' . $this->getDisplaySortCode() . ')';
            case "isa":
                return ($this->getIsJointAccount() ? 'Joint ISA ': 'ISA') . ' (****' . $this->getAccountNumber() . ' / ' . $this->getDisplaySortCode() . ')';
            case "postoffice":
                return ($this->getIsJointAccount() ? 'Joint Post office ': 'Post office') . ' account (****' . $this->getAccountNumber() . ')';
            case "cfo":
                return ($this->getIsJointAccount() ? 'Joint Court funds ': 'Court funds') . ' account (****' . $this->getAccountNumber() . ')';
            case "other":
                return ($this->getIsJointAccount() ? 'Joint other ': 'Other') . ' account ' . ' (****' . $this->getAccountNumber() . ' / ' . $this->getDisplaySortCode() . ')';
            case "other_no_sortcode":
                return ($this->getIsJointAccount() ? 'Joint other ': 'Other') . ' account ' . ' (****' . $this->getAccountNumber() . ')';
        }
    }

    /**
     * Formats a sort code with hyphens
     * 
     * @return string
     */
    public function getDisplaySortCode()
    {
        if (strlen($this->getSortCode()) == 6)
        {
            $sc = str_split($this->getSortCode());
            return $sc[0] . $sc[1] . '-' . $sc[2] . $sc[3] . '-' . $sc[4] . $sc[5];
        }
        return $this->getSortCode();
    }
}
