<?php

namespace AppBundle\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="prof_deputy_estimate_cost")
 * @ORM\Entity
 */
class ProfDeputyEstimateCost
{
    /**
     * @var int
     *
     * @JMS\Groups({"prof-deputy-estimate-costs"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="prof_estimate_cost_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report\Report", inversedBy="profDeputyEstimateCosts")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

    /**
     * @var string a value in self:$profDeputyEstimateCostTypeIds
     *
     * @JMS\Groups({"prof-deputy-estimate-costs"})
     *
     * @ORM\Column(name="prof_deputy_estimate_cost_type_id", type="string", nullable=false)
     */
    private $profDeputyEstimateCostTypeId;

    /**
     * @var float
     *
     * @JMS\Type("string")
     * @JMS\Groups({"prof-deputy-estimate-costs"})
     *
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $amount;

    /**
     * @var bool
     *
     * @JMS\Groups({"prof-deputy-estimate-costs"})
     * @JMS\Type("boolean")
     *
     * @ORM\Column(name="has_more_details", type="boolean", nullable=false)
     */
    private $hasMoreDetails;

    /**
     * @var string
     *
     * @JMS\Groups({"prof-deputy-estimate-costs"})
     *
     * @ORM\Column(name="more_details", type="text", nullable=true)
     */
    private $moreDetails;

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
     * @return ProfDeputyEstimateCost
     */
    public function setReport($report)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return string
     */
    public function getProfDeputyEstimateCostTypeId()
    {
        return $this->profDeputyEstimateCostTypeId;
    }

    /**
     * @param $profDeputyEstimateCostTypeId
     * @return $this
     */
    public function setProfDeputyEstimateCostTypeId($profDeputyEstimateCostTypeId)
    {
        $this->profDeputyEstimateCostTypeId = $profDeputyEstimateCostTypeId;

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
     * @return ProfDeputyEstimateCost
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
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
     * @return ProfDeputyEstimateCost
     */
    public function setMoreDetails($moreDetails)
    {
        $this->moreDetails = $moreDetails;

        return $this;
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
     * @return ProfDeputyEstimateCost
     */
    public function setHasMoreDetails($hasMoreDetails)
    {
        $this->hasMoreDetails = $hasMoreDetails;

        return $this;
    }
}
