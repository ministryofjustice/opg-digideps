<?php

namespace App\Entity\Report;

use App\Entity\Traits\DebtTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="debt")
 *
 * @ORM\Entity
 */
class Debt
{
    use DebtTrait;

    /**
     * Hold debts type
     * 1st value = id, 2nd value = hasMoreInformation.
     *
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
     *
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="debt_id_seq", allocationSize=1, initialValue=1)
     */
    #[JMS\Groups(['debt'])]
    private $id;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Report\Report", inversedBy="debts")
     *
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

    /**
     * @var string a value in self:$debtTypeIds
     *
     *
     * @ORM\Column(name="debt_type_id", type="string", nullable=false)
     */
    #[JMS\Groups(['debt'])]
    private $debtTypeId;

    /**
     * @var string|null
     *
     *
     *
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['debt'])]
    private $amount;

    /**
     * @var bool
     *
     *
     *
     * @ORM\Column(name="has_more_details", type="boolean", nullable=false)
     */
    #[JMS\Groups(['debt'])]
    #[JMS\Type('boolean')]
    private $hasMoreDetails;

    /**
     * @var string
     *
     *
     * @ORM\Column(name="more_details", type="text", nullable=true)
     */
    #[JMS\Groups(['debt'])]
    private $moreDetails;

    /**
     * @param string      $debtTypeId
     * @param bool        $hasMoreDetails
     * @param string|null $amount
     */
    public function __construct(Report $report, $debtTypeId, $hasMoreDetails, $amount = null)
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
     * @return string|null
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
     * @return bool
     */
    public function getHasMoreDetails()
    {
        return $this->hasMoreDetails;
    }

    /**
     * @param bool $hasMoreDetails
     */
    public function setHasMoreDetails($hasMoreDetails)
    {
        $this->hasMoreDetails = $hasMoreDetails;
    }
}
