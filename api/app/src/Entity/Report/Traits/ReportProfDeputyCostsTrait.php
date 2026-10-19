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
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-costs-how-charged'])]
    #[ORM\Column(name: 'prof_dc_how_charged', type: 'string', length: 10, nullable: true)]
    private ?string $profDeputyCostsHowCharged = null;

    /**
     * yes/no/null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-prof-deputy-costs-prev'])]
    #[ORM\Column(name: 'prof_dc_has_previous', type: 'string', length: 3, nullable: true)]
    private ?string $profDeputyCostsHasPrevious = null;

    /**
     * @var Collection<int, ProfDeputyPreviousCost>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\ProfDeputyPreviousCost>')]
    #[JMS\Groups(['report-prof-deputy-costs-prev'])]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: ProfDeputyPreviousCost::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $profDeputyPreviousCosts;

    /**
     * yes/no/null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-prof-deputy-costs-interim'])]
    #[ORM\Column(name: 'prof_dc_has_interim', type: 'string', length: 3, nullable: true)]
    private ?string $profDeputyCostsHasInterim = null;

    /**
     * @var Collection<int, ProfDeputyInterimCost>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\ProfDeputyInterimCost>')]
    #[JMS\Groups(['report-prof-deputy-costs-interim'])]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: ProfDeputyInterimCost::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $profDeputyInterimCosts;

    #[JMS\Type('string')]
    #[JMS\Groups(['report-prof-deputy-fixed-cost'])]
    #[ORM\Column(name: 'prof_dc_fixed_cost_amount', type: 'decimal', precision: 14, scale: 2, nullable: true)]
    private ?string $profDeputyFixedCost = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['report-prof-deputy-costs-scco'])]
    #[ORM\Column(name: 'prof_dc_scco_amount', type: 'decimal', precision: 14, scale: 2, nullable: true)]
    private ?string $profDeputyCostsAmountToScco = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['report-prof-deputy-costs-scco'])]
    #[ORM\Column(name: 'prof_dc_scco_reason_beyond_estimate', type: 'text', nullable: true)]
    private ?string $profDeputyCostsReasonBeyondEstimate = null;

    /**
     * @var Collection<int, ProfDeputyOtherCost>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\ProfDeputyOtherCost>')]
    #[JMS\Groups(['prof-deputy-other-costs'])]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: ProfDeputyOtherCost::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $profDeputyOtherCosts;

    /**
     * Hold prof deputy other costs type
     * 1st value = id, 2nd value = hasMoreInformation.
     *
     * @var array<array{"typeId": string, "hasMoreDetails": bool}>
     */
    #[JMS\Groups(['prof-deputy-other-costs'])]
    public static array $profDeputyOtherCostTypeIds = [
        ['typeId' => 'appointments', 'hasMoreDetails' => false],
        ['typeId' => 'annual-reporting', 'hasMoreDetails' => false],
        ['typeId' => 'conveyancing', 'hasMoreDetails' => false],
        ['typeId' => 'tax-returns', 'hasMoreDetails' => false],
        ['typeId' => 'disbursements', 'hasMoreDetails' => false],
        ['typeId' => 'cost-draftsman', 'hasMoreDetails' => false],
        ['typeId' => 'other', 'hasMoreDetails' => true],
    ];

    /**
     * @return array<array{"typeId": string, "hasMoreDetails": bool}>
     */
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('prof_deputy_other_cost_type_ids')]
    #[JMS\Type('array')]
    #[JMS\Groups(['prof-deputy-other-costs'])]
    public static function getProfDeputyOtherCostTypeIds(): array
    {
        return self::$profDeputyOtherCostTypeIds;
    }

    public function getProfDeputyCostsHowCharged(): ?string
    {
        return $this->profDeputyCostsHowCharged;
    }

    public function setProfDeputyCostsHowCharged(?string $profDeputyCostsHowCharged): static
    {
        $this->profDeputyCostsHowCharged = $profDeputyCostsHowCharged;

        return $this;
    }

    public function getProfDeputyCostsHasPrevious(): ?string
    {
        return $this->profDeputyCostsHasPrevious;
    }

    public function setProfDeputyCostsHasPrevious(?string $profDeputyCostsHasPrevious): static
    {
        $this->profDeputyCostsHasPrevious = $profDeputyCostsHasPrevious;

        return $this;
    }

    /**
     * @return Collection<int, ProfDeputyPreviousCost>
     */
    public function getProfDeputyPreviousCosts(): Collection
    {
        return $this->profDeputyPreviousCosts;
    }

    /**
     * @param Collection<int, ProfDeputyPreviousCost> $profDeputyPreviousCosts
     */
    public function setProfDeputyPreviousCosts(Collection $profDeputyPreviousCosts): void
    {
        $this->profDeputyPreviousCosts = $profDeputyPreviousCosts;
    }

    /**
     * @return Collection<int, ProfDeputyOtherCost>
     */
    public function getProfDeputyOtherCosts(): Collection
    {
        return $this->profDeputyOtherCosts;
    }

    /**
     * @param Collection<int, ProfDeputyOtherCost> $profDeputyOtherCosts
     */
    public function setProfDeputyOtherCosts(Collection $profDeputyOtherCosts): static
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

    public function getProfDeputyOtherCostByTypeId(string $typeId): ?ProfDeputyOtherCost
    {
        $costs = $this->profDeputyOtherCosts->filter(function (ProfDeputyOtherCost $profDeputyOtherCost) use ($typeId): bool {
            return $profDeputyOtherCost->getProfDeputyOtherCostTypeId() == $typeId;
        });

        return $costs->first() ?: null;
    }

    public function getProfDeputyCostsHasInterim(): ?string
    {
        return $this->profDeputyCostsHasInterim;
    }

    public function setProfDeputyCostsHasInterim(?string $profDeputyCostsHasInterim): void
    {
        $this->profDeputyCostsHasInterim = $profDeputyCostsHasInterim;
    }

    /**
     * @return Collection<int, ProfDeputyInterimCost>
     */
    public function getProfDeputyInterimCosts(): Collection
    {
        return $this->profDeputyInterimCosts;
    }

    /**
     * @param Collection<int, ProfDeputyInterimCost> $profDeputyInterimCosts
     */
    public function setProfDeputyInterimCosts(Collection $profDeputyInterimCosts): static
    {
        $this->profDeputyInterimCosts = $profDeputyInterimCosts;

        return $this;
    }

    public function addProfDeputyInterimCosts(ProfDeputyInterimCost $ic): void
    {
        if (!$this->getProfDeputyInterimCosts()->contains($ic)) {
            $this->getProfDeputyInterimCosts()->add($ic);
        }
    }

    public function getProfDeputyCostsAmountToScco(): ?string
    {
        return $this->profDeputyCostsAmountToScco;
    }

    public function setProfDeputyCostsAmountToScco(null|int|float|string $profDeputyCostsAmountToScco): static
    {
        $this->profDeputyCostsAmountToScco = $profDeputyCostsAmountToScco !== null ? (string)$profDeputyCostsAmountToScco : null;

        return $this;
    }

    public function setProfDeputyCostsReasonBeyondEstimate(null|int|float|string $profDeputyCostsReasonBeyondEstimate): static
    {
        $this->profDeputyCostsReasonBeyondEstimate = $profDeputyCostsReasonBeyondEstimate !== null ? (string)$profDeputyCostsReasonBeyondEstimate : null;
        ;

        return $this;
    }

    public function getProfDeputyCostsReasonBeyondEstimate(): ?string
    {
        return $this->profDeputyCostsReasonBeyondEstimate;
    }

    public function getProfDeputyFixedCost(): ?string
    {
        return $this->profDeputyFixedCost;
    }

    public function setProfDeputyFixedCost(null|float|int|string $profDeputyFixedCost): static
    {
        $this->profDeputyFixedCost = $profDeputyFixedCost !== null ? (string)$profDeputyFixedCost : null;

        return $this;
    }

    #[JMS\VirtualProperty]
    #[JMS\Groups(['report-prof-deputy-costs'])]
    public function getProfDeputyTotalCosts(): ?float
    {
        $total = 0.0;

        $onlyFixedTicked = $this->hasProfDeputyCostsHowChargedFixedOnly();

        // return null if data incomplete
        if (
            !$this->getProfDeputyCostsHasPrevious()
            || (!$onlyFixedTicked && !$this->getProfDeputyCostsHasInterim())
            || ($onlyFixedTicked && $this->getProfDeputyFixedCost() === null)
        ) {
            return null;
        }

        foreach ($this->getProfDeputyPreviousCosts() as $previousCost) {
            $total += (float)$previousCost->getAmount();
        }

        // include fixed costs if interim answer is not a "no"
        if ($this->getProfDeputyCostsHasInterim() !== 'yes') {
            $total += (float)$this->getProfDeputyFixedCost();
        }

        if ($this->getProfDeputyCostsHasInterim() === 'yes') {
            foreach ($this->getProfDeputyInterimCosts() as $interimCost) {
                $total += (float)$interimCost->getAmount();
            }
        }

        foreach ($this->getProfDeputyOtherCosts() as $oc) {
            $total += (float)$oc->getAmount();
        }

        return $total;
    }

    #[JMS\VirtualProperty]
    #[JMS\Groups(['report-prof-deputy-costs'])]
    public function getProfDeputyTotalCostsTakenFromClient(): float
    {
        $total = (float)$this->getProfDeputyTotalCosts();

        foreach ($this->getProfDeputyPreviousCosts() as $previousCost) {
            $total -= (float)$previousCost->getAmount();
        }

        return $total;
    }

    public function hasProfDeputyCostsHowChargedFixedOnly(): bool
    {
        return $this->getProfDeputyCostsHowCharged() === Report::PROF_DEPUTY_COSTS_TYPE_FIXED;
    }

    /**
     * Has at least one other cost been submitted? Used to determine whether section is complete as question is last
     * to be asked.
     */
    public function hasProfDeputyOtherCosts(): bool
    {
        return !$this->getProfDeputyOtherCosts()->isEmpty();
    }
}
