<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\ProfDeputyOtherCost;
use AppBundle\Entity\Report\ProfDeputyInterimCost;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportProfDeputyCostsTrait
{
    /**
     * @var boolean
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"prof-deputy-costs-how-charged"})
     * @ORM\Column(name="prof_dc_hc_fixed", type="boolean", nullable=true)
     */
    private $profDeputyCostsHowChargedFixed;

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"prof-deputy-costs-how-charged"})
     * @ORM\Column(name="prof_dc_hc_assessed", type="boolean", nullable=true)
     */
    private $profDeputyCostsHowChargedAssessed;

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"prof-deputy-costs-how-charged"})
     * @ORM\Column(name="prof_dc_hc_agreed", type="boolean", nullable=true)
     */
    private $profDeputyCostsHowChargedAgreed;


    /**
     * @var string yes/no
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report-prof-deputy-costs-prev"})
     * @ORM\Column(name="prof_dc_has_previous", type="string", length=3, nullable=true)
     */
    private $profDeputyCostsHasPrevious;

    /**
     * @JMS\Type("array<AppBundle\Entity\Report\ProfDeputyPreviousCost>")
     * @JMS\Groups({"report-prof-deputy-costs-prev"})
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\ProfDeputyPreviousCost", mappedBy="report", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $profDeputyPreviousCosts;

    /**
     * @var string yes/no
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report-prof-deputy-costs-interim"})
     * @ORM\Column(name="prof_dc_has_interim", type="string", length=3, nullable=true)
     */
    private $profDeputyCostsHasInterim;

    /**
     * @JMS\Type("array<AppBundle\Entity\Report\ProfDeputyInterimCost>")
     * @JMS\Groups({"report-prof-deputy-costs-interim"})
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\ProfDeputyInterimCost", mappedBy="report", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $profDeputyInterimCosts;

    /**
     * @JMS\Type("array<AppBundle\Entity\Report\ProfDeputyOtherCost>")
     * @JMS\Groups({"prof-deputy-other-costs"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\ProfDeputyOtherCost", mappedBy="report", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $profDeputyOtherCosts;

    /**
     * Hold prof deputy other costs type
     * 1st value = id, 2nd value = hasMoreInformation.
     *
     * @JMS\Groups({"prof-deputy-other-costs"})
     *
     * @var array
     */
    public static $profDeputyOtherCostTypeIds = [
        ['typeId' => 'appointments', 'hasMoreDetails' => false],
        ['typeId' => 'annual-reporting', 'hasMoreDetails' => false],
        ['typeId' => 'conveyancing', 'hasMoreDetails' => false],
        ['typeId' => 'tax-returns', 'hasMoreDetails' => false],
        ['typeId' => 'disbursements', 'hasMoreDetails' => false],
        ['typeId' => 'cost-draftsman', 'hasMoreDetails' => false],
        ['typeId' => 'other', 'hasMoreDetails' => true],
    ];

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("prof_deputy_other_cost_type_ids")
     * @JMS\Type("array")
     * @JMS\Groups({"prof-deputy-other-costs"})
     *
     * @return array
     */
    public static function getProfDeputyOtherCostTypeIds()
    {
        return self::$profDeputyOtherCostTypeIds;
    }

    /**
     * @param array $profDeputyOtherCostTypeIds
     */
    public static function setProfDeputyOtherCostTypeIds($profDeputyOtherCostTypeIds)
    {
        self::$profDeputyOtherCostTypeIds = $profDeputyOtherCostTypeIds;
    }

    /**
     * @var float
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report-prof-deputy-costs-scco"})
     * @ORM\Column(name="prof_dc_scco_amount", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $profDeputyCostsAmountToScco;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report-prof-deputy-costs-scco"})
     * @ORM\Column(name="prof_dc_scco_reason_beyond_estimate", type="text", nullable=true)
     */
    private $profDeputyCostsReasonBeyondEstimate;

    /**
     * @return boolean
     */
    public function getProfDeputyCostsHowChargedFixed()
    {
        return $this->profDeputyCostsHowChargedFixed;
    }

    /**
     * @param string $profDeputyCostsHowChargedFixed
     * @return ReportProfDeputyCostsTrait
     */
    public function setProfDeputyCostsHowChargedFixed($profDeputyCostsHowChargedFixed)
    {
        $this->profDeputyCostsHowChargedFixed = $profDeputyCostsHowChargedFixed;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getProfDeputyCostsHowChargedAssessed()
    {
        return $this->profDeputyCostsHowChargedAssessed;
    }

    /**
     * @param string $profDeputyCostsHowChargedAssessed
     * @return ReportProfDeputyCostsTrait
     */
    public function setProfDeputyCostsHowChargedAssessed($profDeputyCostsHowChargedAssessed)
    {
        $this->profDeputyCostsHowChargedAssessed = $profDeputyCostsHowChargedAssessed;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getProfDeputyCostsHowChargedAgreed()
    {
        return $this->profDeputyCostsHowChargedAgreed;
    }

    /**
     * @param string $profDeputyCostsHowChargedAgreed
     * @return ReportProfDeputyCostsTrait
     */
    public function setProfDeputyCostsHowChargedAgreed($profDeputyCostsHowChargedAgreed)
    {
        $this->profDeputyCostsHowChargedAgreed = $profDeputyCostsHowChargedAgreed;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfDeputyCostsHasPrevious()
    {
        return $this->profDeputyCostsHasPrevious;
    }

    /**
     * @param string $profDeputyCostsHasPrevious
     * @return ReportProfDeputyCostsTrait
     */
    public function setProfDeputyCostsHasPrevious($profDeputyCostsHasPrevious)
    {
        $this->profDeputyCostsHasPrevious = $profDeputyCostsHasPrevious;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProfDeputyPreviousCosts()
    {
        return $this->profDeputyPreviousCosts;
    }

    /**
     * @param mixed $profDeputyPreviousCosts
     */
    public function setProfDeputyPreviousCosts($profDeputyPreviousCosts)
    {
        $this->profDeputyPreviousCosts = $profDeputyPreviousCosts;
    }

   /**
    * @return ProfDeputyOtherCost[]
    */
    public function getProfDeputyOtherCosts()
    {
        return $this->profDeputyOtherCosts;
    }

    /**
     * @param $profDeputyOtherCosts
     * @return $this
     */
    public function setProfDeputyOtherCosts($profDeputyOtherCosts)
    {
        $this->profDeputyOtherCosts = $profDeputyOtherCosts;
        return $this;
    }

    /**
     * @param ProfDeputyOtherCost $profDeputyOtherCost
     * @return $this
     */
    public function addProfDeputyOtherCost(ProfDeputyOtherCost $profDeputyOtherCost)
    {
        if (!$this->profDeputyOtherCosts->contains($profDeputyOtherCost)) {
            $this->profDeputyOtherCosts->add($profDeputyOtherCost);
        }

        return $this;
    }

    /**
     * @param string $typeId
     *
     * @return ProfDeputyOtherCost
     */
    public function getProfDeputyOtherCostByTypeId($typeId)
    {
        return $this->getProfDeputyOtherCosts()->filter(function (ProfDeputyOtherCost $profDeputyOtherCost) use ($typeId) {
            return $profDeputyOtherCost->getProfDeputyOtherCostTypeId() == $typeId;
        })->first();
    }

    /**
     * @return string
     */
    public function getProfDeputyCostsHasInterim()
    {
        return $this->profDeputyCostsHasInterim;
    }

    /**
     * @param string $profDeputyCostsHasInterim
     */
    public function setProfDeputyCostsHasInterim($profDeputyCostsHasInterim)
    {
        $this->profDeputyCostsHasInterim = $profDeputyCostsHasInterim;
    }

    /**
     * @return mixed
     */
    public function getProfDeputyInterimCosts()
    {
        return $this->profDeputyInterimCosts;
    }

    /**
     * @param mixed $profDeputyInterimCosts
     * @return ReportProfDeputyCostsTrait
     */
    public function setProfDeputyInterimCosts($profDeputyInterimCosts)
    {
        $this->profDeputyInterimCosts = $profDeputyInterimCosts;
        return $this;
    }

    /**
     * @param ProfDeputyInterimCost $ic
     */
    public function addProfDeputyInterimCosts(ProfDeputyInterimCost $ic)
    {
        if (!$this->getProfDeputyInterimCosts()->contains($ic)) {
            $this->getProfDeputyInterimCosts()->add($ic);
        }
    }

    /**
     * @return float
     */
    public function getProfDeputyCostsAmountToScco()
    {
        return $this->profDeputyCostsAmountToScco;
    }

    /**
     * @param float $profDeputyCostsAmountToScco
     * @return ReportProfDeputyCostsTrait
     */
    public function setProfDeputyCostsAmountToScco($profDeputyCostsAmountToScco)
    {
        $this->profDeputyCostsAmountToScco = $profDeputyCostsAmountToScco;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfDeputyCostsBeyondEstimateReason()
    {
        return $this->profDeputyCostsReasonBeyondEstimate;
    }

    /**
     * @param string $profDeputyCostsReasonBeyondEstimate
     * @return ReportProfDeputyCostsTrait
     */
    public function setProfDeputyCostsReasonBeyondEstimate($profDeputyCostsReasonBeyondEstimate)
    {
        $this->profDeputyCostsReasonBeyondEstimate = $profDeputyCostsReasonBeyondEstimate;
        return $this;
    }

}
