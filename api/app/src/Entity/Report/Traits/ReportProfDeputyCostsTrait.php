<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Report\ProfDeputyInterimCost;
use OPG\Digideps\Backend\Entity\Report\ProfDeputyOtherCost;
use OPG\Digideps\Backend\Entity\Report\ProfDeputyPreviousCost;
use OPG\Digideps\Backend\Entity\Report\Report;

trait ReportProfDeputyCostsTrait
{
    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-costs-how-charged'])]
    #[ORM\Column(name: 'prof_dc_how_charged', type: 'string', length: 10, nullable: true)]
    private $profDeputyCostsHowCharged;

    /**
     * @var string yes/no
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-prof-deputy-costs-prev'])]
    #[ORM\Column(name: 'prof_dc_has_previous', type: 'string', length: 3, nullable: true)]
    private $profDeputyCostsHasPrevious;

    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\ProfDeputyPreviousCost>')]
    #[JMS\Groups(['report-prof-deputy-costs-prev'])]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: ProfDeputyPreviousCost::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $profDeputyPreviousCosts;

    /**
     * @var string yes/no
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-prof-deputy-costs-interim'])]
    #[ORM\Column(name: 'prof_dc_has_interim', type: 'string', length: 3, nullable: true)]
    private $profDeputyCostsHasInterim;

    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\ProfDeputyInterimCost>')]
    #[JMS\Groups(['report-prof-deputy-costs-interim'])]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: ProfDeputyInterimCost::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $profDeputyInterimCosts;

    /**
     * @var float
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-prof-deputy-fixed-cost'])]
    #[ORM\Column(name: 'prof_dc_fixed_cost_amount', type: 'decimal', precision: 14, scale: 2, nullable: true)]
    private $profDeputyFixedCost;

    /**
     * @var float
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-prof-deputy-costs-scco'])]
    #[ORM\Column(name: 'prof_dc_scco_amount', type: 'decimal', precision: 14, scale: 2, nullable: true)]
    private $profDeputyCostsAmountToScco;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-prof-deputy-costs-scco'])]
    #[ORM\Column(name: 'prof_dc_scco_reason_beyond_estimate', type: 'text', nullable: true)]
    private $profDeputyCostsReasonBeyondEstimate;

    /**
     * @var Collection<int, ProfDeputyOtherCost>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\ProfDeputyOtherCost>')]
    #[JMS\Groups(['prof-deputy-other-costs'])]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: ProfDeputyOtherCost::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $profDeputyOtherCosts;

    /**
     * Hold prof deputy other costs type
     * 1st value = id, 2nd value = hasMoreInformation.
     *
     * @var array
     */
    #[JMS\Groups(['prof-deputy-other-costs'])]
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
     * @return array
     */
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('prof_deputy_other_cost_type_ids')]
    #[JMS\Type('array')]
    #[JMS\Groups(['prof-deputy-other-costs'])]
    public static function getProfDeputyOtherCostTypeIds(): array
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
     */
    public function setProfDeputyCostsHowCharged($profDeputyCostsHowCharged): static
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
     */
    public function setProfDeputyCostsHasPrevious($profDeputyCostsHasPrevious): static
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
     * @return Collection<int, ProfDeputyOtherCost>
     */
    public function getProfDeputyOtherCosts()
    {
        return $this->profDeputyOtherCosts;
    }

    public function setProfDeputyOtherCosts($profDeputyOtherCosts): static
    {
        $this->profDeputyOtherCosts = $profDeputyOtherCosts;

        return $this;
    }

    public function addProfDeputyOtherCost(ProfDeputyOtherCost $profDeputyOtherCost): static
    {
        if (!$this->profDeputyOtherCosts->contains($profDeputyOtherCost)) {
            $this->profDeputyOtherCosts->add($profDeputyOtherCost);
        }

        return $this;
    }

    /**
     * @param string $typeId
     *
     * @return ?ProfDeputyOtherCost
     */
    public function getProfDeputyOtherCostByTypeId($typeId)
    {
        $costs = $this->profDeputyOtherCosts->filter(function (ProfDeputyOtherCost $profDeputyOtherCost) use ($typeId) {
            return $profDeputyOtherCost->getProfDeputyOtherCostTypeId() == $typeId;
        });

        return $costs->first() ?? null;
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
     */
    public function setProfDeputyInterimCosts($profDeputyInterimCosts): static
    {
        $this->profDeputyInterimCosts = $profDeputyInterimCosts;

        return $this;
    }

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
     */
    public function setProfDeputyCostsAmountToScco($profDeputyCostsAmountToScco): static
    {
        $this->profDeputyCostsAmountToScco = $profDeputyCostsAmountToScco;

        return $this;
    }

    /**
     * @param string $profDeputyCostsReasonBeyondEstimate
     */
    public function setProfDeputyCostsReasonBeyondEstimate($profDeputyCostsReasonBeyondEstimate): static
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
     */
    public function setProfDeputyFixedCost($profDeputyFixedCost): static
    {
        $this->profDeputyFixedCost = $profDeputyFixedCost;

        return $this;
    }

    /**
     * @return float
     */
    #[JMS\VirtualProperty]
    #[JMS\Groups(['report-prof-deputy-costs'])]
    public function getProfDeputyTotalCosts()
    {
        $total = 0;

        $onlyFixedTicked = $this->hasProfDeputyCostsHowChargedFixedOnly();

        // return null if data incomplete
        if (
            !$this->getProfDeputyCostsHasPrevious()
            || (!$onlyFixedTicked && !$this->getProfDeputyCostsHasInterim())
            || ($onlyFixedTicked && null === $this->getProfDeputyFixedCost())
        ) {
            return null;
        }

        foreach ($this->getProfDeputyPreviousCosts() as $previousCost) {
            $total += (float) $previousCost->getAmount();
        }

        // include fixed costs if interim answer is not a "no"
        if ('yes' !== $this->getProfDeputyCostsHasInterim()) {
            $total += $this->getProfDeputyFixedCost();
        }

        if ('yes' === $this->getProfDeputyCostsHasInterim()) {
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
     */
    #[JMS\VirtualProperty]
    #[JMS\Groups(['report-prof-deputy-costs'])]
    public function getProfDeputyTotalCostsTakenFromClient()
    {
        $total = $this->getProfDeputyTotalCosts();

        foreach ($this->getProfDeputyPreviousCosts() as $previousCost) {
            $total -= (float) $previousCost->getAmount();
        }

        return $total;
    }

    /**
     * @return bool
     */
    public function hasProfDeputyCostsHowChargedFixedOnly()
    {
        return Report::PROF_DEPUTY_COSTS_TYPE_FIXED == $this->getProfDeputyCostsHowCharged();
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
