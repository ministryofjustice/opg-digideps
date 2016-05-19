<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Account.
 *
 * @ORM\Table(name="account")
 * @ORM\Entity()
 */
class Account
{
    /**
     * Keep in sync with client.
     *
     * @JMS\Exclude 
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
     * @var int
     * @JMS\Groups({"transactions", "basic", "transfers"})
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="account_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     * @JMS\Groups({"transactions", "basic", "transfers"})
     * @ORM\Column(name="bank_name", type="string", length=500, nullable=true)
     */
    private $bank;

    /**
     * @var string
     * @JMS\Groups({"transactions", "basic", "transfers"})
     *
     * @ORM\Column(name="account_type", type="string", length=125, nullable=true)
     */
    private $accountType;

    /**
     * @var string
     * @JMS\Groups({"transactions", "basic", "transfers"})
     * 
     * @ORM\Column(name="sort_code", type="string", length=6, nullable=true)
     */
    private $sortCode;

    /**
     * @var string
     * @JMS\Groups({"transactions", "basic", "transfers"})
     * 
     * @ORM\Column(name="account_number", type="string", length=4, nullable=true)
     */
    private $accountNumber;

    /**
     * @var \DateTime
     * @JMS\Groups({"transactions", "basic", "transfers"})
     * 
     * @ORM\Column(name="last_edit", type="datetime", nullable=true)
     */
    private $lastEdit;

    /**
     * @var \DateTime
     * @JMS\Groups({"transactions", "basic", "transfers"})
     * 
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var decimal
     * @JMS\Groups({"transactions", "basic", "transfers"})
     * @JMS\Type("string")
     * 
     * @ORM\Column(name="opening_balance", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $openingBalance;

    /**
     * @var decimal
     * 
     * @JMS\Type("string")
     * @JMS\Groups({"transactions", "basic", "transfers"})
     * 
     * @ORM\Column(name="closing_balance", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $closingBalance;

    /**
     * @var bool
     * @JMS\Groups({"transactions", "basic", "transfers"})
     * @JMS\Type("boolean")
     * @ORM\Column(name="is_closed", type="boolean")
     */
    private $isClosed;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report", inversedBy="accounts")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
    private $report;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\MoneyTransfer", mappedBy="from", cascade={"remove"})
     */
    private $transfersFrom;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\MoneyTransfer", mappedBy="to", cascade={"remove"})
     */
    private $transfersTo;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"transactions", "basic", "transfers"})
     *
     * @ORM\Column(name="is_joint_account", type="string", length=3, nullable=true)
     */
    private $isJointAccount;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"transactions", "basic", "transfers"})
     * 
     * @ORM\Column(name="meta", type="text", nullable=true)
     */
    private $meta;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->lastEdit = null;
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
     * Set bank.
     *
     * @param string $bank
     *
     * @return Account
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
     * @JMS\SerializedName("account_type_text")
     * @JMS\Groups({"transactions", "basic", "transfers"})
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
     * @return Account
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
     * @return Account
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
     * Set lastEdit.
     *
     * @param \DateTime $lastEdit
     *
     * @return Account
     */
    public function setLastEdit($lastEdit)
    {
        $this->lastEdit = $lastEdit;

        return $this;
    }

    /**
     * Get lastEdit.
     *
     * @return \DateTime
     */
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

        return $this;
    }

    /**
     * Set openingBalance.
     *
     * @param string $openingBalance
     *
     * @return Account
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
     * @return Account
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

    public function getIsClosed()
    {
        return $this->isClosed;
    }

    public function setIsClosed($isClosed)
    {
        $this->isClosed = $isClosed;

        return $this;
    }

    /**
     * Set report.
     *
     * @param Report $report
     *
     * @return Account
     */
    public function setReport(Report $report = null)
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
     * Sort code required.
     *
     * @return string
     */
    public function requiresBankNameAndSortCode()
    {
        return !in_array($this->getAccountType(), ['postoffice', 'cfo']);
    }

    public function getIsJointAccount()
    {
        return $this->isJointAccount;
    }

    /**
     * @param string $isJointAccount yes/no/null
     * 
     * @return \AppBundle\Entity\Account
     */
    public function setIsJointAccount($isJointAccount)
    {
        $this->isJointAccount = trim(strtolower($isJointAccount));

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
}
