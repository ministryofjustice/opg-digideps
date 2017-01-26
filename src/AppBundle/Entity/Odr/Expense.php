<?php

namespace AppBundle\Entity\Odr;

use JMS\Serializer\Annotation as JMS;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="odr_expense")
 */
class Expense
{
    /**
     * @var int
     *
     * @JMS\Groups({"odr-expenses"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="odr_expense_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"odr-expenses"})
     * @ORM\Column(name="explanation", type="text", nullable=false)
     */
    private $explanation;

    /**
     * @var float
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-expenses"})
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=true)
     *
     * @var string
     */
    private $amount;

    /**
     * @var Odr
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Odr\Odr", inversedBy="expenses")
     * @ORM\JoinColumn(name="odr_id", referencedColumnName="id")
     */
    private $odr;

    /**
     * Expense constructor.
     *
     * @param Odr    $odr
     * @param string $explanation
     * @param $amount
     */
    public function __construct(Odr $odr)
    {
        $this->odr = $odr;
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
     * @return mixed
     */
    public function getExplanation()
    {
        return $this->explanation;
    }

    /**
     * @param mixed $explanation
     *
     * @return Expense
     */
    public function setExplanation($explanation)
    {
        $this->explanation = $explanation;

        return $this;
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
     *
     * @return Expense
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

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
     */
    public function setOdr($odr)
    {
        $this->odr = $odr;
    }
}
