<?php

namespace AppBundle\Entity\Odr;

use JMS\Serializer\Annotation as JMS;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="expense")
 */
class Expense
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="expense_id_seq", allocationSize=1, initialValue=1)
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
    public function __construct(Odr $odr, $explanation, $amount)
    {
        $this->odr = $odr;
        $odr->addExpense($this);
        $this->explanation = $explanation;
        $this->amount = $amount;
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
}
