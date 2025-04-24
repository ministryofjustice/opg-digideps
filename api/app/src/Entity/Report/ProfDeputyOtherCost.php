<?php

namespace App\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="prof_deputy_other_cost")
 *
 * @ORM\Entity
 */
class ProfDeputyOtherCost
{
    /**
     * @var int
     *
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="prof_other_cost_id_seq", allocationSize=1, initialValue=1)
     */
    #[JMS\Groups(['prof-deputy-other-costs'])]
    private $id;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Report\Report", inversedBy="profDeputyOtherCosts")
     *
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

    /**
     * @var string a value in self:$profDeputyOtherCostTypeIds
     *
     *
     * @ORM\Column(name="prof_deputy_other_cost_type_id", type="string", nullable=false)
     */
    #[JMS\Groups(['prof-deputy-other-costs'])]
    private $profDeputyOtherCostTypeId;

    /**
     * @var float
     *
     *
     *
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-other-costs'])]
    private $amount;

    /**
     * @var bool
     *
     *
     *
     * @ORM\Column(name="has_more_details", type="boolean", nullable=false)
     */
    #[JMS\Groups(['prof-deputy-other-costs'])]
    #[JMS\Type('boolean')]
    private $hasMoreDetails;

    /**
     * @var string
     *
     *
     * @ORM\Column(name="more_details", type="text", nullable=true)
     */
    #[JMS\Groups(['prof-deputy-other-costs'])]
    private $moreDetails;

    /**
     * @param string $profDeputyOtherCostTypeId
     * @param bool   $hasMoreDetails
     * @param float  $amount
     */
    public function __construct(
        Report $report,
        $profDeputyOtherCostTypeId,
        $hasMoreDetails,
        $amount
    ) {
        $this->report = $report;
        /* @todo implement this methood without pulling in the trait containing the method */
        // $report->addProfDeputyOtherCost($this);

        $this->profDeputyOtherCostTypeId = $profDeputyOtherCostTypeId;
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
    public function getProfDeputyOtherCostTypeId()
    {
        return $this->profDeputyOtherCostTypeId;
    }

    /**
     * @return $this
     */
    public function setProfDeputyOtherCostTypeId($profDeputyOtherCostTypeId)
    {
        $this->profDeputyOtherCostTypeId = $profDeputyOtherCostTypeId;

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
