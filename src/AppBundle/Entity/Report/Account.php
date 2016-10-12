<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Traits\HasReportTrait;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

class Account
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
        'postoffice' => 'Post office account',
        'cfo' => 'Court funds office account',
        'other' => 'Other',
    ];

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="account.bank.notBlank", groups={"bank_name"})
     * @Assert\Length(max=500, min=2,  minMessage= "account.bank.minMessage", maxMessage= "account.bank.maxMessage", groups={"bank_name"})
     * 
     * @JMS\Groups({"account"})
     * 
     * @var string
     */
    private $bank;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="account.accountType.notBlank", groups={"add_edit"})
     * @Assert\Length(max=100, maxMessage= "account.accountType.maxMessage", groups={"add_edit"})
     *
     * @JMS\Groups({"account"})
     *
     * @var string
     */
    private $accountType;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $accountTypeText;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"account"})
     * 
     * @var string
     */
    private $sortCode;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="account.accountNumber.notBlank", groups={"add_edit"})
     * @Assert\Type(type="numeric", message="account.accountNumber.type", groups={"add_edit"})
     * @Assert\Length(exactMessage="account.accountNumber.length",min=4, max=4, groups={"add_edit"})
     * @JMS\Groups({"account"})
     * 
     * @var string
     */
    private $accountNumber;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"account"})
     *
     * @Assert\NotBlank(message="account.openingBalance.notBlank", groups={"add_edit"})
     * @Assert\Type(type="numeric", message="account.openingBalance.type", groups={"add_edit"})
     * @Assert\Range(max=10000000000, maxMessage = "account.openingBalance.outOfRange", groups={"add_edit"})
     *
     * @var decimal
     */
    private $openingBalance;

    /**
     * @JMS\Type("string")
     * @Assert\Type(type="numeric", message="account.closingBalance.type", groups={"closing_balance", "add_edit"})
     * @Assert\Range(max=10000000000, maxMessage = "account.closingBalance.outOfRange", groups={"closing_balance", "add_edit"})
     * @JMS\Groups({"account"})
     * 
     * @var decimal
     */
    private $closingBalance;

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"account"})
     *
     * @var bool
     */
    private $isClosed;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"account"})
     * @Assert\NotBlank(message="account.isJointAccount.notBlank", groups={"add_edit"})
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
     * @param type $closingBalance
     *
     * @return type
     */
    public function setClosingBalance($closingBalance)
    {
        $this->closingBalance = $closingBalance;

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
     * Only for partial account created during migrations
     * e.g. asset -> bank account.
     * 
     * @return bool
     */
    public function hasMissingInformation()
    {
        if (!$this->getAccountNumber() || $this->getIsJointAccount() === null) {
            return true;
        }

        if ($this->requiresBankNameAndSortCode()) {
            if (!$this->getBank() || !$this->getSortCode()) {
                return true;
            }
        }

        return false;
    }
}
