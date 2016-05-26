<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="debt")
 * @ORM\Entity
 */
class Debt
{
    /**
     * Hold debts type
     * 1st value = id, 2nd value = hasMoreInformation
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
     * @JMS\Groups({"debts"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="debt_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report", inversedBy="debts")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
    private $report;

    /**
     * @var string
     * @JMS\Groups({"debts"})
     *
     * @ORM\Column(type="string", nullable=false)
     */
    private $debtTypeId;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"debts"})
     *
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $amount;

    /**
     * @var string
     * @JMS\Groups({"debts"})
     *
     * @ORM\Column(name="more_details", type="text", nullable=true)
     */
    private $moreDetails;

    /**
     * @var bool
     * @JMS\Groups({"debts"})
     *
     * @JMS\Type("boolean")
     */
    private $hasMoreDetails;

    /**
     * Debt constructor.
     *
     * @param Report $report
     * @param string $debtTypeId
     * @param boole $hasMoreDetails
     * @param float $amount
     */
    public function __construct(Report $report, $debtTypeId, $hasMoreDetails, $amount)
    {
        $this->report = $report;
        $report->addDebt($this);

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
     * @return boolean
     */
    public function getHasMoreDetails()
    {
        return $this->hasMoreDetails;
    }

    /**
     * @param boolean $hasMoreDetails
     */
    public function setHasMoreDetails($hasMoreDetails)
    {
        $this->hasMoreDetails = $hasMoreDetails;
    }

    


}
