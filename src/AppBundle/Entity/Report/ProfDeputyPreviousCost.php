<?php

namespace AppBundle\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use DateTime;

/**
 * MoneyTransfer.
 *
 * @ORM\Table(name="prof_deputy_prev_cost")
 * @ORM\Entity
 */
class ProfDeputyPreviousCost
{
    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\Groups({"prof-deputy-costs-prev"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="prof_deputy_prev_cost_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report\Report", inversedBy="profDeputyPrevCosts")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

    /**
     * @var DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"prof-deputy-costs-prev"})
     *
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     */
    private $startDate;


    /**
     * @var DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"prof-deputy-costs-prev"})
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    private $endDate;


    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"prof-deputy-costs-prev"})
     *
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $amount;

    /**
     * ProfDeputyPreviousCost constructor.
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    /**
     * @return DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param DateTime $startDate
     * @return ProfDeputyPreviousCost
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param DateTime $endDate
     * @return ProfDeputyPreviousCost
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
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
     * @return ProfDeputyPreviousCost
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

}
