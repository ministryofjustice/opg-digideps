<?php

namespace App\Entity\Report;

use App\Entity\Traits\IsSoftDeleteableEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="money_transaction_short")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *      "in"   = "App\Entity\Report\MoneyTransactionShortIn",
 *      "out"  = "App\Entity\Report\MoneyTransactionShortOut"
 * })
 */
abstract class MoneyTransactionShort implements MoneyTransactionInterface
{
    use IsSoftDeleteableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="money_transaction_short_id_seq", allocationSize=1, initialValue=1)
     * @JMS\Groups({"moneyTransactionsShortIn", "moneyTransactionsShortOut"})
     */
    private $id;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Report\Report", inversedBy="moneyTransactionsShort")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

    /**
     * @var float
     *
     * @JMS\Type("string")
     * @JMS\Groups({"moneyTransactionsShortIn", "moneyTransactionsShortOut"})
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=false)
     */
    private $amount;

    /**
     * @var string
     *
     * @JMS\Groups({"moneyTransactionsShortIn", "moneyTransactionsShortOut"})
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"moneyTransactionsShortIn", "moneyTransactionsShortOut"})
     * @ORM\Column(name="date", type="date", nullable=true, options={ "default": null })
     */
    private $date;

    /**
     * Discriminator field.
     *
     * @var string
     *
     * @JMS\Exclude
     */
    private $type;

    /**
     * @return MoneyTransactionShort
     */
    public static function factory(string $type, Report $report)
    {
        switch ($type) {
            case 'in':
                return new MoneyTransactionShortIn($report);
            case 'out':
                return new MoneyTransactionShortOut($report);
        }
        throw new \InvalidArgumentException(__METHOD__.': type not recognised');
    }

    /**
     * MoneyTransactionShort constructor.
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
     *
     * @return MoneyTransactionShort
     */
    public function setId($id)
    {
        $this->id = $id;

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
     *
     * @return MoneyTransactionShort
     */
    public function setReport($report)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     *
     * @return MoneyTransactionShort
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return MoneyTransactionShort
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     *
     * @return MoneyTransactionShort
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
