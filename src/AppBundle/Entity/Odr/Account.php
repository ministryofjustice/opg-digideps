<?php

namespace AppBundle\Entity\Odr;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Account.
 *
 * @ORM\Table(name="odr_account")
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
     * @JMS\Groups({"odr-account"})
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="odr_account_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     * @JMS\Groups({"odr-account"})
     * @ORM\Column(name="bank_name", type="string", length=500, nullable=true)
     */
    private $bank;

    /**
     * @var string
     * @JMS\Groups({"odr-account"})
     *
     * @ORM\Column(name="account_type", type="string", length=125, nullable=true)
     */
    private $accountType;

    /**
     * @var string
     * @JMS\Groups({"odr-account"})
     * 
     * @ORM\Column(name="sort_code", type="string", length=6, nullable=true)
     */
    private $sortCode;

    /**
     * @var string
     * @JMS\Groups({"odr-account"})
     * 
     * @ORM\Column(name="account_number", type="string", length=4, nullable=true)
     */
    private $accountNumber;

    /**
     * @var \DateTime
     * @JMS\Groups({"odr-account"})
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var decimal
     * @JMS\Groups({"odr-account"})
     * @JMS\Type("string")
     * 
     * @ORM\Column(name="balance_on_cod", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $balanceOnCourtOrderDate;

    /**
     * @var Odr
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Odr\Odr", inversedBy="bankAccounts")
     * @ORM\JoinColumn(name="odr_id", referencedColumnName="id")
     */
    private $odr;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->lastEdit = null;
        $this->createdAt = new \DateTime();
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
     * @return null
     */
    public function getLastEdit()
    {
        return $this->lastEdit;
    }

    /**
     * @param null $lastEdit
     * @return Account
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
     * @return Account
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
     * @return Account
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
     * @return Account
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
     * @return Account
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
     * @return Account
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
        return $this;
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
     * @return Account
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
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
     * @return Account
     */
    public function setBalanceOnCourtOrderDate($balanceOnCourtOrderDate)
    {
        $this->balanceOnCourtOrderDate = $balanceOnCourtOrderDate;
        return $this;
    }

    /**
     * @return Odr
     */
    public function getOdr()
    {
        return $this->odr;
    }

    /**
     * @param Odr $odr
     * @return Account
     */
    public function setOdr($odr)
    {
        $this->odr = $odr;
        return $this;
    }




}
