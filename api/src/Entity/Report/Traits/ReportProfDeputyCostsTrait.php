<?php

namespace App\Entity\Report\Traits;

use App\Entity\Report\ProfDeputyEstimateCost;
use App\Entity\Report\ProfDeputyOtherCost;
use App\Entity\Report\ProfDeputyInterimCost;
use App\Entity\Report\Report;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportProfDeputyCostsTrait
{

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"prof-deputy-costs-how-charged"})
     * @ORM\Column(name="prof_dc_how_charged", type="string", length=10, nullable=true)
     */
    private $profDeputyCostsHowCharged;

    /**
     * @var string yes/no
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report-prof-deputy-costs-prev"})
     * @ORM\Column(name="prof_dc_has_previous", type="string", length=3, nullable=true)
     */
    private $profDeputyCostsHasPrevious;

    /**
     * @JMS\Type("array<App\Entity\Report\ProfDeputyPreviousCost>")
     * @JMS\Groups({"report-prof-deputy-costs-prev"})
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Report\ProfDeputyPreviousCost", mappedBy="report", cascade={"persist", "remove"})
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
     * @JMS\Type("array<App\Entity\Report\ProfDeputyInterimCost>")
     * @JMS\Groups({"report-prof-deputy-costs-interim"})
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Report\ProfDeputyInterimCost", mappedBy="report", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $profDeputyInterimCosts;

    /**
     * @var float
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report-prof-deputy-fixed-cost"})
     * @ORM\Column(name="prof_dc_fixed_cost_amount", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $profDeputyFixedCost;

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
     * @JMS\Type("array<App\Entity\Report\ProfDeputyOtherCost>")
     * @JMS\Groups({"prof-deputy-other-costs"})
     * @ORM\OneToMany(targetEntity="App\Entity\Report\ProfDeputyOtherCost", mappedBy="report", cascade={"persist", "remove"})
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
     * @return string
     */
    public function getProfDeputyCostsHowCharged()
    {
        return $this->profDeputyCostsHowCharged;
    }

    /**
     * @param string $profDeputyCostsHowCharged
     * @return ReportProfDeputyCostsTrait
     */
    public function setProfDeputyCostsHowCharged($profDeputyCostsHowCharged)
    {
        $this->profDeputyCostsHowCharged = $profDeputyCostsHowCharged;
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
     * @param string $profDeputyCostsReasonBeyondEstimate
     * @return ReportProfDeputyCostsTrait
     */
    public function setProfDeputyCostsReasonBeyondEstimate($profDeputyCostsReasonBeyondEstimate)
    {
        $this->profDeputyCostsReasonBeyondEstimate = $profDeputyCostsReasonBeyondEstimate;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfDeputyCostsReasonBeyondEstimate()
    {
        return $this->profDeputyCostsReasonBeyondEstimate;
    }

    /**
     * @return float
     */
    public function getProfDeputyFixedCost()
    {
        return $this->profDeputyFixedCost;
    }

    /**
     * @param float $profDeputyFixedCost
     * @return ReportProfDeputyCostsTrait
     */
    public function setProfDeputyFixedCost($profDeputyFixedCost)
    {
        $this->profDeputyFixedCost = $profDeputyFixedCost;
        return $this;
    }

    /**
     * @return float
     * @JMS\VirtualProperty()
     * @JMS\Groups({"report-prof-deputy-costs"})
     */
    public function getProfDeputyTotalCosts()
    {
        $total = 0;

        $onlyFixedTicked = $this->hasProfDeputyCostsHowChargedFixedOnly();

        // return null if data incomplete
        if (!$this->getProfDeputyCostsHasPrevious()
            || (!$onlyFixedTicked && !$this->getProfDeputyCostsHasInterim())
            || ($onlyFixedTicked && null === $this->getProfDeputyFixedCost())
        ) {
            return null;
        }

        foreach ($this->getProfDeputyPreviousCosts() as $previousCost) {
            $total += (float) $previousCost->getAmount();
        }

        // include fixed costs if interim answer is not a "no"
        if ($this->getProfDeputyCostsHasInterim() !== 'yes') {
            $total += (float) $this->getProfDeputyFixedCost();
        }

        if ($this->getProfDeputyCostsHasInterim() === 'yes') {
            foreach ($this->getProfDeputyInterimCosts() as $interimCost) {
                $total += (float) $interimCost->getAmount();
            }
        }

        foreach ($this->getProfDeputyOtherCosts() as $oc) {
            $total += (float) $oc->getAmount();
        }

        return $total;
    }

    /**
     * @return float
     * @JMS\VirtualProperty()
     * @JMS\Groups({"report-prof-deputy-costs"})
     */
    public function getProfDeputyTotalCostsTakenFromClient()
    {
        $total = $this->getProfDeputyTotalCosts();

        foreach ($this->getProfDeputyPreviousCosts() as $previousCost) {
            $total -= (float) $previousCost->getAmount();
        }

        return $total;
    }

    /**
     * @return boolean
     */
    public function hasProfDeputyCostsHowChargedFixedOnly()
    {
        return $this->getProfDeputyCostsHowCharged() == Report::PROF_DEPUTY_COSTS_TYPE_FIXED;
    }

    /**
     * Has at least one other cost been submitted? Used to determine whether section is complete as question is last
     * to be asked.
     *
     * @return bool
     */
    public function hasProfDeputyOtherCosts()
    {
        return !$this->getProfDeputyOtherCosts()->isEmpty();
    }
}
