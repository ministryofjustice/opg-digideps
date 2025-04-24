<?php

namespace App\Entity\Report;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * MoneyTransfer.
 *
 * @ORM\Table(name="prof_deputy_interim_cost")
 *
 * @ORM\Entity
 */
class ProfDeputyInterimCost
{
    /**
     * @var int
     *
     *
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="prof_deputy_interim_cost_id_seq", allocationSize=1, initialValue=1)
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['prof-deputy-costs-interim'])]
    private $id;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Report\Report", inversedBy="profDeputyInterimCosts")
     *
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

    /**
     * @var \DateTime
     *
     *
     *
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['prof-deputy-costs-interim'])]
    private $date;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-costs-interim'])]
    private $amount;

    /**
     * ProfDeputyInterimCost constructor.
     *
     * @param string $amount
     */
    public function __construct(Report $report, \DateTime $date, $amount)
    {
        $this->report = $report;
        $this->date = $date;
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
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
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
     */
    public function setDate($date)
    {
        $this->date = $date;
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
     * @return ProfDeputyPreviousCost
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }
}
