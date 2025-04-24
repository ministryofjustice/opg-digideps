<?php

namespace App\Entity\Report;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * MoneyTransfer.
 *
 * @ORM\Table(name="prof_deputy_prev_cost")
 *
 * @ORM\Entity
 */
class ProfDeputyPreviousCost
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
     * @ORM\SequenceGenerator(sequenceName="prof_deputy_prev_cost_id_seq", allocationSize=1, initialValue=1)
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['prof-deputy-costs-prev'])]
    private $id;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Report\Report", inversedBy="profDeputyPreviousCosts")
     *
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

    /**
     * @var \DateTime
     *
     *
     *
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     */
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['prof-deputy-costs-prev'])]
    private $startDate;

    /**
     * @var \DateTime
     *
     *
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['prof-deputy-costs-prev'])]
    private $endDate;

    /**
     * @var float
     *
     *
     *
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-costs-prev'])]
    private $amount;

    /**
     * ProfDeputyPreviousCost constructor.
     *
     * @param float $amount
     */
    public function __construct(Report $report, $amount)
    {
        $this->report = $report;
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
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     *
     * @return ProfDeputyPreviousCost
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     *
     * @return ProfDeputyPreviousCost
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

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
     * @return ProfDeputyPreviousCost
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }
}
