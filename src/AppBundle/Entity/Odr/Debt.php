<?php

namespace AppBundle\Entity\Odr;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="odr_debt")
 * @ORM\Entity
 */
class Debt
{
    /**
     * Hold debts type
     * 1st value = id, 2nd value = hasMoreInformation.
     *
     * @var array
     */
    public static $debtTypeIds = [
        ['care-fees', false],
        ['credit-cards', false],
        ['loans', false],
        ['other', true],
    ];

    /**
     * @var int
     * @JMS\Groups({"odr-debt"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="debt_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Odr
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Odr\Odr", inversedBy="debts")
     * @ORM\JoinColumn(name="odr_id", referencedColumnName="id")
     */
    private $odr;

    /**
     * @var string
     * @JMS\Groups({"odr-debt"})
     *
     * @ORM\Column(name="debt_type_id", type="string", nullable=false)
     */
    private $debtTypeId;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-debt"})
     *
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $amount;

    /**
     * @var bool
     * @JMS\Groups({"odr-debt"})
     * @JMS\Type("boolean")
     *
     * @ORM\Column(name="has_more_details", type="boolean", nullable=false)
     */
    private $hasMoreDetails;

    /**
     * @var string
     * @JMS\Groups({"odr-debt"})
     *
     * @ORM\Column(name="more_details", type="text", nullable=true)
     */
    private $moreDetails;

    /**
     * Debt constructor.
     *
     * @param Odr    $odr
     * @param string $debtTypeId
     * @param boole  $hasMoreDetails
     * @param float  $amount
     */
    public function __construct(Odr $odr, $debtTypeId, $hasMoreDetails, $amount)
    {
        $this->odr = $odr;
        $odr->addDebt($this);

        $this->debtTypeId = $debtTypeId;
        $this->hasMoreDetails = $hasMoreDetails;
        $this->amount = $amount;
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
     * @return Odr
     */
    public function getOdr()
    {
        return $this->odr;
    }

    /**
     * @param Odr $odr
     */
    public function setOdr($odr)
    {
        $this->odr = $odr;
    }

    /**
     * @return string
     */
    public function getDebtTypeId()
    {
        return $this->debtTypeId;
    }

    /**
     * @param string $debtTypeId
     */
    public function setDebtTypeId($debtTypeId)
    {
        $this->debtTypeId = $debtTypeId;
    }

    /**
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param string $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getMoreDetails()
    {
        return $this->moreDetails;
    }

    /**
     * @param string $moreDetails
     */
    public function setMoreDetails($moreDetails)
    {
        $this->moreDetails = $moreDetails;
    }

    /**
     * @return bool
     */
    public function getHasMoreDetails()
    {
        return $this->hasMoreDetails;
    }

    /**
     * @param bool $hasMoreDetails
     */
    public function setHasMoreDetails($hasMoreDetails)
    {
        $this->hasMoreDetails = $hasMoreDetails;
    }
}
