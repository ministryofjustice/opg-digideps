<?php

namespace App\Entity\Ndr;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;


#[ORM\Table(name: 'odr_expense')]
#[ORM\Entity]
class Expense
{
    /**
     * @var int
     *
     * @JMS\Groups({"ndr-expenses"})
     */
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'odr_expense_id_seq', allocationSize: 1, initialValue: 1)]
    private $id;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"ndr-expenses"})
     */
    #[ORM\Column(name: 'explanation', type: 'text', nullable: false)]
    private $explanation;

    /**
     * @var float
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"ndr-expenses"})
     *
     *
     * @var string
     */
    #[ORM\Column(name: 'amount', type: 'decimal', precision: 14, scale: 2, nullable: true)]
    private $amount;

    /**
     * @var Ndr
     */
    #[ORM\JoinColumn(name: 'odr_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Ndr::class, inversedBy: 'expenses')]
    private $ndr;

    /**
     * Expense constructor.
     */
    public function __construct(Ndr $ndr)
    {
        $this->ndr = $ndr;
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
     * @return Ndr
     */
    public function getNdr()
    {
        return $this->ndr;
    }

    /**
     * @param Ndr $ndr
     */
    public function setNdr($ndr)
    {
        $this->ndr = $ndr;
    }
}
