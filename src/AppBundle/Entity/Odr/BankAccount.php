<?php

namespace AppBundle\Entity\Odr;

use AppBundle\Entity\Odr\Traits\HasOdrTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class BankAccount
{
    use HasOdrTrait;

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
        'other_no_sortcode' => 'Other without sort code',
    ];

    private static $typesRequiringSortCode = [
        'postoffice',
        'cfo',
        'other_no_sortcode'
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
     * @Assert\NotBlank(message="odr.account.accountType.notBlank", groups={"bank-account-type"})
     * @Assert\Length(max=100, maxMessage="odr.account.accountType.maxMessage", groups={"bank-account-type"})
     *
     * @var string
     */
    private $accountType;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"bank-account"})
     *
     * @Assert\NotBlank(message="odr.account.bank.notBlank", groups={"bank-account-name"})
     * @Assert\Length(max=500, min=2,  minMessage="odr.account.bank.minMessage", maxMessage="odr.account.bank.maxMessage", groups={"bank-account-name"})
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
     * @Assert\NotBlank(message="odr.account.accountNumber.notBlank", groups={"bank-account-number"})
     * @Assert\Type(type="alnum", message="odr.account.accountNumber.type", groups={"bank-account-number"})
     * @Assert\Length(exactMessage="odr.account.accountNumber.length",min=4, max=4, groups={"bank-account-number"})
     *
     * @var string
     */
    private $accountNumber;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"bank-account"})
     *
     * @Assert\NotBlank(message="odr.account.balanceOnCourtOrderDate.notBlank", groups={"bank-account-balance-on-cot"})
     * @Assert\Type(type="numeric", message="odr.account.balanceOnCourtOrderDate.type", groups={"bank-account-balance-on-cot"})
     * @Assert\Range(max=10000000000, maxMessage="odr.account.balanceOnCourtOrderDate.outOfRange", groups={"bank-account-balance-on-cot"})
     *
     * @var decimal
     */
    private $balanceOnCourtOrderDate;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"bank-account"})
     *
     * @Assert\NotBlank(message="odr.account.isJointAccount.notBlank", groups={"bank-account-is-joint"})
     *
     * @var string
     */
    private $isJointAccount;

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
     * Sort code required.
     *
     * @return string
     */
    public function requiresBankNameAndSortCode()
    {
        return !in_array($this->getAccountType(), self::$typesRequiringSortCode);
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
}
