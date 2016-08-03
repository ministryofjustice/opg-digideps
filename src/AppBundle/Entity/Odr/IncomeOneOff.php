<?php

namespace AppBundle\Entity\Odr;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="income_one_off")
 * @ORM\Entity
 */
class IncomeOneOff
{
    public static $ids = [
        'bequest_or_inheritance',
        'cash_gift_received',
        'refunds',
        'sale_of_an_asset',
        'sale_of_investment',
        'sale_of_property',
    ];

    /**
     * @var int
     * @JMS\Groups({"odr-income-one-off"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="income_one_off_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Odr
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Odr\Odr", inversedBy="incomeOneOff")
     * @ORM\JoinColumn(name="odr_id", referencedColumnName="id")
     */
    private $odr;

    /**
     * @var string
     * @JMS\Groups({"odr-income-one-off"})
     * @ORM\Column(name="type_id", type="string", nullable=false)
     */
    private $typeId;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-income-one-off"})
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $amount;

    /**
     * Debt constructor.
     *
     * @param Odr $odr
     * @param string $typeId
     * @param float $amount
     */
    public function __construct(Odr $odr, $typeId, $amount)
    {
        $this->odr = $odr;
        $this->typeId = $typeId;
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
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * @param string $typeId
     * @return IncomeOneOff
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;
        return $this;
    }


}
