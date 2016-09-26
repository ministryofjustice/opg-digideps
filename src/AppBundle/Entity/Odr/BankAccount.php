<?php

namespace AppBundle\Entity\Odr;

use AppBundle\Entity\Traits\HasOdrTrait;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

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
     * @Assert\NotBlank(message="odr.account.bank.notBlank", groups={"basic", "bank_name"})
     * @Assert\Length(max=500, min=2,  minMessage="odr.account.bank.minMessage", maxMessage="odr.account.bank.maxMessage", groups={"basic", "bank_name"})
     *
     * @var string
     */
    private $bank;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"bank-account"})
     *
     * @Assert\NotBlank(message="odr.account.accountType.notBlank", groups={"basic", "add_edit"})
     * @Assert\Length(max=100, maxMessage="odr.account.accountType.maxMessage", groups={"basic", "add_edit"})
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
     * @JMS\Groups({"bank-account"})
     *
     * @Assert\NotBlank( message="odr.account.sortCode.notBlank", groups={"basic", "sortcode"})
     * @Assert\Type(type="numeric", message="odr.account.sortCode.type", groups={"basic", "sortcode"})
     * @Assert\Length(min=6, max=6, exactMessage="odr.account.sortCode.length", groups={"basic", "sortcode"})
     *
     * @var string
     */
    private $sortCode;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"bank-account"})
     *
     * @Assert\NotBlank(message="odr.account.accountNumber.notBlank", groups={"basic", "add_edit"})
     * @Assert\Type(type="numeric", message="odr.account.accountNumber.type", groups={"basic", "add_edit"})
     * @Assert\Length(exactMessage="odr.account.accountNumber.length",min=4, max=4, groups={"basic", "add_edit"})
     *
     * @var string
     */
    private $accountNumber;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"bank-account"})
     *
     * @Assert\NotBlank(message="odr.account.balanceOnCourtOrderDate.notBlank", groups={"basic", "add_edit"})
     * @Assert\Type(type="numeric", message="odr.account.balanceOnCourtOrderDate.type", groups={"basic", "add_edit"})
     * @Assert\Range(max=10000000000, maxMessage="odr.account.balanceOnCourtOrderDate.outOfRange", groups={"basic", "add_edit"})
     *
     * @var decimal
     */
    private $balanceOnCourtOrderDate;

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
        return !in_array($this->getAccountType(), ['postoffice', 'cfo']);
    }
}
