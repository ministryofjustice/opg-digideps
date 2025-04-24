<?php

namespace App\Entity\Report;

use App\Entity\Report\Traits\HasBankAccountTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Used for both
 * - Lay deputy expenses
 * - PA Fees outside practice direction.
 *
 * @ORM\Entity()
 *
 * @ORM\Table(name="expense")
 */
class Expense
{
    use HasBankAccountTrait;

    /**
     * @var int
     *
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="expense_id_seq", allocationSize=1, initialValue=1)
     */
    #[JMS\Groups(['expenses'])]
    private $id;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="explanation", type="text", nullable=false)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['expenses'])]
    private $explanation;

    /**
     * @var float
     *
     *
     *
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=true)
     *
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['expenses'])]
    private $amount;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Report\Report", inversedBy="expenses")
     *
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

    /**
     * Expense constructor.
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
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
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param Report $report
     */
    public function setReport($report)
    {
        $this->report = $report;
    }
}
