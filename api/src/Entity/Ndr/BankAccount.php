<?php

namespace App\Entity\Ndr;

use App\Entity\BankAccountInterface;
use App\Entity\Traits\Timestamps;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Account.
 *
 * @ORM\Table(name="odr_account")
 * @ORM\Entity(repositoryClass="App\Repository\NdrBankAccountRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class BankAccount implements BankAccountInterface
{
    use Timestamps;
    
    /**
     * Keep in sync with client.
     *
     * @JMS\Exclude
     */
    public static $types = [
        'current' => 'Current account',
        'savings' => 'Savings account',
        'isa' => 'ISA',
        'postoffice' => 'Post Office account',
        'cfo' => 'Court Funds Office account',
        'other' => 'Other',
        'other_no_sortcode' => 'Other without sort code',
    ];

    /**
     * Keep in sync with client.
     *
     * @JMS\Exclude
     */
    private static $typesNotRequiringSortCode = [
        'postoffice',
        'cfo',
        'other_no_sortcode',
    ];

    /**
     * Keep in sync with client.
     *
     * @JMS\Exclude
     */
    private static $typesNotRequiringBankName = [
        'postoffice',
        'cfo',
    ];

    /**
     * @var int
     * @JMS\Groups({"ndr-account"})
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="odr_account_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     * @JMS\Groups({"ndr-account"})
     * @ORM\Column(name="bank_name", type="string", length=500, nullable=true)
     */
    private $bank;

    /**
     * @var string
     * @JMS\Groups({"ndr-account"})
     *
     * @ORM\Column(name="account_type", type="string", length=125, nullable=true)
     */
    private $accountType;

    /**
     * @var string
     * @JMS\Groups({"ndr-account"})
     *
     * @ORM\Column(name="sort_code", type="string", length=6, nullable=true)
     */
    private $sortCode;

    /**
     * @var string
     * @JMS\Groups({"ndr-account"})
     *
     * @ORM\Column(name="account_number", type="string", length=4, nullable=true)
     */
    private $accountNumber;

    /**
     * @var DateTime
     * @JMS\Groups({"ndr-account"})
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var float
     * @JMS\Groups({"ndr-account"})
     * @JMS\Type("string")
     *
     * @ORM\Column(name="balance_on_cod", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $balanceOnCourtOrderDate;

    /**
     * @var Ndr
     * @JMS\Groups({"bank-acccount-ndr"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Ndr\Ndr", inversedBy="bankAccounts")
     * @ORM\JoinColumn(name="odr_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ndr;

    /**
     * @var string yes|no|null
     *
     * @JMS\Type("string")
     * @JMS\Groups({"ndr-account"})
     *
     * @ORM\Column(name="is_joint_account", type="string", length=3, nullable=true)
     */
    private $isJointAccount;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->lastEdit = null;
        $this->createdAt = new DateTime();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("account_type_text")
     * @JMS\Groups({"ndr-account"})
     *
     * @return string
     */
    public function getAccountTypeText()
    {
        $type = $this->getAccountType();

        return isset(self::$types[$type]) ? self::$types[$type] : null;
    }

    /**
     * Bank name required.
     *
     * @return bool
     */
    public function requiresBankName()
    {
        return !in_array($this->getAccountType(), self::$typesNotRequiringBankName);
    }

    /**
     * Sort code required.
     *
     * @return bool
     */
    public function requiresSortCode()
    {
        return !in_array($this->getAccountType(), self::$typesNotRequiringSortCode);
    }

    public function getLastEdit()
    {
        return $this->lastEdit;
    }

    /**
     * @param null $lastEdit
     *
     * @return BankAccount
     */
    public function setLastEdit($lastEdit)
    {
        $this->lastEdit = $lastEdit;

        return $this;
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
     *
     * @return BankAccount
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     *
     * @return BankAccount
     */
    public function setBank($bank)
    {
        $this->bank = $bank;

        return $this;
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
     *
     * @return BankAccount
     */
    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;

        return $this;
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
     *
     * @return BankAccount
     */
    public function setSortCode($sortCode)
    {
        $this->sortCode = $sortCode;

        return $this;
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
     *
     * @return BankAccount
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return decimal
     */
    public function getBalanceOnCourtOrderDate()
    {
        return $this->balanceOnCourtOrderDate;
    }

    /**
     * @return decimal
     */
    public function getOpeningBalance()
    {
        return $this->getBalanceOnCourtOrderDate();
    }

    /**
     * @return decimal
     */
    public function getClosingBalance()
    {
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
     * @param decimal $balanceOnCourtOrderDate
     *
     * @return BankAccount
     */
    public function setBalanceOnCourtOrderDate($balanceOnCourtOrderDate)
    {
        $this->balanceOnCourtOrderDate = $balanceOnCourtOrderDate;

        return $this;
    }

    /**
     * @return Ndr
     */
    public function getNdr()
    {
        return $this->ndr;
    }

    /**
     * @param Ndr $ndr
     *
     * @return BankAccount
     */
    public function setNdr($ndr)
    {
        $this->ndr = $ndr;

        return $this;
    }

    public function getIsJointAccount()
    {
        return $this->isJointAccount;
    }

    /**
     * @param string $isJointAccount yes/no/null
     *
     * @return \App\Entity\Report\BankAccount
     */
    public function setIsJointAccount($isJointAccount)
    {
        $this->isJointAccount = trim(strtolower($isJointAccount));

        return $this;
    }

    /**
     * Get bank account name in one line
     * <bank> - <type> (****<last 4 digits>)
     * e.g.
     * barclays - Current account (****1234)
     * Natwest - ISA (****4444).
     *
     * @JMS\VirtualProperty
     * @JMS\SerializedName("name_one_line")
     * @JMS\Groups({"account"})
     *
     * @return string
     */
    public function getNameOneLine()
    {
        return (!empty($this->getBank()) ? $this->getBank().' - ' : '')
            .$this->getAccountTypeText()
            .' (****'.$this->getAccountNumber().')';
    }
}
