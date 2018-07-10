<?php

namespace AppBundle\Entity\Ndr;

use AppBundle\Entity\BankAccountInterface;
use AppBundle\Entity\Ndr\Traits\HasNdrTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class BankAccount implements BankAccountInterface
{
    use HasNdrTrait;

    /**
     * Keep in sync with api.
     */
    public static $types = [
        'current',
        'savings',
        'isa',
        'postoffice',
        'cfo',
        'other',
        'other_no_sortcode',
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
     * @JMS\Groups({"bank-account"})
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"bank-account"})
     *
     * @Assert\NotBlank(message="ndr.account.accountType.notBlank", groups={"bank-account-type"})
     * @Assert\Length(max=100, maxMessage="ndr.account.accountType.maxMessage", groups={"bank-account-type"})
     *
     * @var string
     */
    private $accountType;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"bank-account"})
     *
     * @Assert\NotBlank(message="ndr.account.bank.notBlank", groups={"bank-account-name"})
     * @Assert\Length(max=500, min=2,  minMessage="ndr.account.bank.minMessage", maxMessage="ndr.account.bank.maxMessage", groups={"bank-account-name"})
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
     * @JMS\Groups({"bank-account"})
     *
     * @var string
     */
    private $sortCode;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"bank-account"})
     *
     * @Assert\NotBlank(message="ndr.account.accountNumber.notBlank", groups={"bank-account-number"})
     * @Assert\Type(type="alnum", message="ndr.account.accountNumber.type", groups={"bank-account-number"})
     * @Assert\Length(exactMessage="ndr.account.accountNumber.length",min=4, max=4, groups={"bank-account-number"})
     *
     * @var string
     */
    private $accountNumber;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"bank-account"})
     *
     * @Assert\NotBlank(message="ndr.account.balanceOnCourtOrderDate.notBlank", groups={"bank-account-balance-on-cot"})
     * @Assert\Type(type="numeric", message="ndr.account.balanceOnCourtOrderDate.type", groups={"bank-account-balance-on-cot"})
     * @Assert\Range(max=10000000000, maxMessage="ndr.account.balanceOnCourtOrderDate.outOfRange", groups={"bank-account-balance-on-cot"})
     *
     * @var decimal
     */
    private $balanceOnCourtOrderDate;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"bank-account"})
     *
     * @Assert\NotBlank(message="ndr.account.isJointAccount.notBlank", groups={"bank-account-is-joint"})
     *
     * @var string
     */
    private $isJointAccount;

    /**
     * Get bank account name in one line. Comes from Virtual property.
     *
     * <bank> - <type> (****<last 4 digits>)
     * e.g.
     * barclays - Current account (****1234)
     *
     * @JMS\Type("string")
     * @JMS\Groups({"bank-account"})
     *
     * @var string
     */
    private $nameOneLine;

    /**
     * @return mixed
     */
    public static function getTypes()
    {
        return self::$types;
    }

    /**
     * @param mixed $types
     */
    public static function setTypes($types)
    {
        self::$types = $types;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * @param string $bank
     */
    public function setBank($bank)
    {
        $this->bank = $bank;
    }

    /**
     * @return string
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * @param string $accountType
     */
    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;
    }

    /**
     * @return string
     */
    public function getAccountTypeText()
    {
        return $this->accountTypeText;
    }

    /**
     * @param string $accountTypeText
     */
    public function setAccountTypeText($accountTypeText)
    {
        $this->accountTypeText = $accountTypeText;
    }

    /**
     * @return string
     */
    public function getSortCode()
    {
        return $this->sortCode;
    }

    /**
     * @param string $sortCode
     */
    public function setSortCode($sortCode)
    {
        $this->sortCode = $sortCode;
    }

    /**
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * @param string $accountNumber
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
    }

    /**
     * @return decimal
     */
    public function getOpeningBalance()
    {
        return $this->openingBalance;
    }

    /**
     * @param decimal $openingBalance
     */
    public function setOpeningBalance($openingBalance)
    {
        $this->openingBalance = $openingBalance;
    }

    /**
     * @return decimal
     */
    public function getBalanceOnCourtOrderDate()
    {
        return $this->balanceOnCourtOrderDate;
    }

    /**
     * @param decimal $balanceOnCourtOrderDate
     */
    public function setBalanceOnCourtOrderDate($balanceOnCourtOrderDate)
    {
        $this->balanceOnCourtOrderDate = $balanceOnCourtOrderDate;
    }

    /**
     * @return decimal
     */
    public function getClosingBalance() {
        return $this->getBalanceOnCourtOrderDate();
    }

    /**
     * @return false
     */
    public function getIsClosed()
    {
        return false;
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

    public function getIsJointAccount()
    {
        return $this->isJointAccount;
    }

    public function setIsJointAccount($isJointAccount)
    {
        $this->isJointAccount = $isJointAccount;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameOneLine()
    {
        return $this->nameOneLine;
    }

    /**
     * @param string $nameOneLine
     * @return $this
     */
    public function setNameOneLine($nameOneLine)
    {
        $this->nameOneLine = $nameOneLine;
        return $this;
    }

}
