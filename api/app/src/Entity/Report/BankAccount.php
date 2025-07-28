<?php

namespace App\Entity\Report;

use App\Entity\BankAccountInterface;
use App\Entity\Traits\CreateUpdateTimestamps;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Account.
 *
 * @ORM\Table(name="account")
 *
 * @ORM\Entity()
 *
 * @ORM\HasLifecycleCallbacks()
 */
class BankAccount implements BankAccountInterface
{
    use CreateUpdateTimestamps;

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
     *
     * @JMS\Groups({"account"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="account_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Groups({"account"})
     *
     * @ORM\Column(name="bank_name", type="string", length=500, nullable=true)
     */
    private $bank;

    /**
     * @var string
     *
     * @JMS\Groups({"account"})
     *
     * @ORM\Column(name="account_type", type="string", length=125, nullable=true)
     */
    private $accountType;

    /**
     * @var string
     *
     * @JMS\Groups({"account"})
     *
     * @ORM\Column(name="sort_code", type="string", length=6, nullable=true)
     */
    private $sortCode;

    /**
     * @var string
     *
     * @JMS\Groups({"account"})
     *
     * @ORM\Column(name="account_number", type="string", length=4, nullable=true)
     */
    private $accountNumber;

    /**
     * @var float
     *
     * @JMS\Groups({"account"})
     *
     * @JMS\Type("string")
     *
     * @ORM\Column(name="opening_balance", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $openingBalance;

    /**
     * @var float
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"account"})
     *
     * @ORM\Column(name="closing_balance", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $closingBalance;

    /**
     * @var bool
     *
     * @JMS\Groups({"account"})
     *
     * @JMS\Type("boolean")
     *
     * @ORM\Column(name="is_closed", type="boolean", options={ "default": false}, nullable=true)
     */
    private $isClosed;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Report\Report", inversedBy="bankAccounts")
     *
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

    /**
     * @var string yes|no|null
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"account"})
     *
     * @ORM\Column(name="is_joint_account", type="string", length=3, nullable=true)
     */
    private $isJointAccount;

    /**
     * @deprecated hold information about previous data migration
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"account"})
     *
     * @ORM\Column(name="meta", type="text", nullable=true)
     */
    private $meta;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->isClosed = false;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @return int
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set bank.
     *
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
     * Get bank.
     *
     * @return string
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * @return string
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * @JMS\VirtualProperty
     *
     * @JMS\SerializedName("account_type_text")
     *
     * @JMS\Groups({"account"})
     *
     * @return string
     */
    public function getAccountTypeText()
    {
        $type = $this->getAccountType();

        return isset(self::$types[$type]) ? self::$types[$type] : null;
    }

    /**
     * @param string $accountType
     */
    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;
    }

    /**
     * Set sortCode.
     *
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
     * Get sortCode.
     *
     * @return string
     */
    public function getSortCode()
    {
        return $this->sortCode;
    }

    /**
     * Set accountNumber.
     *
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
     * Get accountNumber.
     *
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * Set openingBalance.
     *
     * @param string $openingBalance
     *
     * @return BankAccount
     */
    public function setOpeningBalance($openingBalance)
    {
        $this->openingBalance = $openingBalance;

        return $this;
    }

    /**
     * Get openingBalance.
     *
     * @return string
     */
    public function getOpeningBalance()
    {
        return $this->openingBalance;
    }

    /**
     * Set closingBalance.
     *
     * @param string $closingBalance
     *
     * @return BankAccount
     */
    public function setClosingBalance($closingBalance)
    {
        $this->closingBalance = $closingBalance;

        return $this;
    }

    /**
     * Get closingBalance.
     *
     * @return string
     */
    public function getClosingBalance()
    {
        return $this->closingBalance;
    }

    /**
     * @return bool
     */
    public function getIsClosed()
    {
        return $this->isClosed;
    }

    /**
     * @param bool $isClosed
     *
     * @return BankAccount
     */
    public function setIsClosed($isClosed)
    {
        $this->isClosed = $isClosed;

        return $this;
    }

    /**
     * Set report.
     *
     * @return BankAccount
     */
    public function setReport(?Report $report = null)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report.
     *
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
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

    public function getIsJointAccount()
    {
        return $this->isJointAccount;
    }

    /**
     * @param string $isJointAccount yes/no/null
     *
     * @return BankAccount
     */
    public function setIsJointAccount($isJointAccount)
    {
        if (!is_null($isJointAccount)) {
            $this->isJointAccount = trim(strtolower($isJointAccount));
        }

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
     * Get bank account name in one line
     * <bank> - <type> (****<last 4 digits>)
     * e.g.
     * barclays - Current account (****1234)
     * Natwest - ISA (****4444).
     *
     * @JMS\VirtualProperty
     *
     * @JMS\SerializedName("name_one_line")
     *
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
