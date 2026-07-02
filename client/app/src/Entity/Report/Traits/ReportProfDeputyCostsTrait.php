<?php

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use OPG\Digideps\Frontend\Entity\Report\ProfDeputyInterimCost;
use OPG\Digideps\Frontend\Entity\Report\ProfDeputyOtherCost;
use OPG\Digideps\Frontend\Entity\Report\ProfDeputyPreviousCost;
use OPG\Digideps\Frontend\Entity\Report\Report;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait ReportProfDeputyCostsTrait
{
    /**
     * @Assert\NotBlank(message="profDeputyCostsHowCharged.notBlank", groups={"prof-deputy-costs-how-charged"} )
     * @JMS\Type("string")
     * @JMS\Groups({"deputyCostsHowCharged"})
     */
    private ?string $profDeputyCostsHowCharged;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"profDeputyCostsHasPrevious"})
     */
    private ?string $profDeputyCostsHasPrevious;

    /**
     * @var ProfDeputyOtherCost[]
     *
     * @JMS\Type("array<OPG\Digideps\Frontend\Entity\Report\ProfDeputyOtherCost>")
     * @JMS\Groups({"prof-deputy-other-costs"})
     */
    private array $profDeputyOtherCosts = [];

    private array $profDeputyOtherCostIds;

    /**
     * @var ProfDeputyPreviousCost[]
     *
     * @JMS\Type("array<OPG\Digideps\Frontend\Entity\Report\ProfDeputyPreviousCost>")
     */
    private array $profDeputyPreviousCosts = [];

    /**
     * @Assert\NotBlank( message="profDeputyFixedCost.amount.notBlank", groups={"prof-deputy-fixed-cost"} )
     * @Assert\Range(min=0, minMessage = "profDeputyFixedCost.amount.minMessage", groups={"prof-deputy-fixed-cost"})
     * @JMS\Type("double")
     * @JMS\Groups({"profDeputyFixedCost"})
     */
    private ?float $profDeputyFixedCost = null;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"profDeputyCostsHasInterim"})
     */
    private ?string $profDeputyCostsHasInterim;

    /**
     * @var ProfDeputyInterimCost[]
     * @JMS\Groups({"profDeputyInterimCosts"})
     * @JMS\Type("array<OPG\Digideps\Frontend\Entity\Report\ProfDeputyInterimCost>")
     */
    private array $profDeputyInterimCosts = [];

    /**
     * @Assert\NotBlank( message="profDeputyCostsScco.amountToScco.notBlank", groups={"prof-deputy-costs-scco"} )
     * @Assert\Range(min=0, minMessage = "profDeputyCostsScco.amountToScco.minMessage", groups={"prof-deputy-costs-scco"})
     * @JMS\Type("double")
     * @JMS\Groups({"profDeputyCostsScco"})
     */
    private ?float $profDeputyCostsAmountToScco = null;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"profDeputyCostsScco"})
     */
    private ?string $profDeputyCostsReasonBeyondEstimate;

    /**
     * @JMS\Type("double")
     */
    private ?float $profDeputyTotalCosts = null;

    /**
     * @JMS\Type("double")
     */
    private ?float $profDeputyTotalCostsTakenFromClient = null;

    /**
     * return true if only fixed is true.
     */
    public function hasProfDeputyCostsHowChargedFixedOnly(): bool
    {
        return $this->getProfDeputyCostsHowCharged() == Report::PROF_DEPUTY_COSTS_TYPE_FIXED;
    }

    /**
     * @JMS\Type("array")
     * @JMS\Groups({"prof-deputy-other-costs"})
     */
    private array $profDeputyOtherCostTypeIds = [];

    public function getProfDeputyOtherCostTypeIds(): array
    {
        return $this->profDeputyOtherCostTypeIds;
    }

    public function setProfDeputyOtherCostTypeIds(array $profDeputyOtherCostTypeIds): static
    {
        $this->profDeputyOtherCostTypeIds = $profDeputyOtherCostTypeIds;

        return $this;
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

    public function profCostsInterimAtLeastOne(ExecutionContextInterface $context): void
    {
        $ics = $this->getProfDeputyInterimCosts();
        $emptyCount = 0;

        foreach ($ics as $index => $ic) {
            if ($ics[$index]->getDate() === null && $ics[$index]->getAmount() === null) {
                ++$emptyCount;
                continue;
            }

            if ($ics[$index]->getDate() === null) {
                $context->buildViolation('profDeputyInterimCost.date.notBlank')->atPath(sprintf('profDeputyInterimCosts[%s].date', $index))->addViolation();
            }

            if ($ics[$index]->getAmount() === null) {
                $context->buildViolation('profDeputyInterimCost.amount.notBlank')->atPath(sprintf('profDeputyInterimCosts[%s].amount', $index))->addViolation();
            }
        }

        if ($emptyCount === count($ics)) {
            $context->buildViolation('profDeputyInterimCost.atLeastOne')->addViolation();
        }
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
     * @return ProfDeputyPreviousCost[]
     */
    public function getProfDeputyPreviousCosts(): array
    {
        return $this->profDeputyPreviousCosts;
    }

    /**
     * @param ProfDeputyPreviousCost[] $profDeputyPreviousCosts
     * @return $this
     */
    public function setProfDeputyPreviousCosts(array $profDeputyPreviousCosts): static
    {
        $this->profDeputyPreviousCosts = $profDeputyPreviousCosts;

        return $this;
    }

    public function getProfDeputyCostsHasInterim(): ?string
    {
        return $this->profDeputyCostsHasInterim;
    }

    public function setProfDeputyCostsHasInterim(?string $profDeputyCostsHasInterim): static
    {
        $this->profDeputyCostsHasInterim = $profDeputyCostsHasInterim;

        return $this;
    }

    /**
     * @return ProfDeputyInterimCost[]
     */
    public function getProfDeputyInterimCosts(): array
    {
        return $this->profDeputyInterimCosts;
    }

    /**
     * @param ProfDeputyInterimCost[] $profDeputyInterimCosts
     *
     * @return $this
     */
    public function setProfDeputyInterimCosts(array $profDeputyInterimCosts): static
    {
        $this->profDeputyInterimCosts = $profDeputyInterimCosts;

        return $this;
    }

    /**
     * @return ProfDeputyOtherCost[]
     */
    public function getProfDeputyOtherCosts(): array
    {
        return $this->profDeputyOtherCosts;
    }

    /**
     * @param ProfDeputyOtherCost[] $profDeputyOtherCosts
     *
     * @return $this
     */
    public function setProfDeputyOtherCosts(array $profDeputyOtherCosts): static
    {
        $this->profDeputyOtherCosts = $profDeputyOtherCosts;

        return $this;
    }

    public function addProfDeputyInterimCosts(ProfDeputyInterimCost $ic): void
    {
        $this->profDeputyInterimCosts[] = $ic;
    }

    public function getProfDeputyCostsAmountToScco(): ?float
    {
        return $this->profDeputyCostsAmountToScco;
    }

    /**
     * @return $this
     */
    public function setProfDeputyCostsAmountToScco(?float $profDeputyCostsAmountToScco): static
    {
        $this->profDeputyCostsAmountToScco = $profDeputyCostsAmountToScco;

        return $this;
    }

    public function getProfDeputyCostsReasonBeyondEstimate(): ?string
    {
        return $this->profDeputyCostsReasonBeyondEstimate;
    }

    public function setProfDeputyCostsReasonBeyondEstimate(?string $profDeputyCostsReasonBeyondEstimate): static
    {
        $this->profDeputyCostsReasonBeyondEstimate = $profDeputyCostsReasonBeyondEstimate;

        return $this;
    }

    public function getProfDeputyOtherCostIds(): array
    {
        return $this->profDeputyOtherCostIds;
    }

    public function setProfDeputyOtherCostIds(array $profDeputyOtherCostIds): static
    {
        $this->profDeputyOtherCostIds = $profDeputyOtherCostIds;
        return $this;
    }

    public function getProfDeputyFixedCost(): ?float
    {
        return $this->profDeputyFixedCost;
    }

    public function setProfDeputyFixedCost(?float $profDeputyFixedCost): static
    {
        $this->profDeputyFixedCost = $profDeputyFixedCost;

        return $this;
    }

    public function getProfDeputyTotalCosts(): ?float
    {
        return $this->profDeputyTotalCosts;
    }

    public function setProfDeputyTotalCosts(?float $profDeputyTotalCosts): static
    {
        $this->profDeputyTotalCosts = $profDeputyTotalCosts;

        return $this;
    }

    public function getProfDeputyTotalCostsTakenFromClient(): ?float
    {
        return $this->profDeputyTotalCostsTakenFromClient;
    }

    public function setProfDeputyTotalCostsTakenFromClient(?float $profDeputyTotalCostsTakenFromClient): static
    {
        $this->profDeputyTotalCostsTakenFromClient = $profDeputyTotalCostsTakenFromClient;

        return $this;
    }

    protected function getProfDeputyOtherCostByTypeId(string $typeId): ?ProfDeputyOtherCost
    {
        foreach ($this->getProfDeputyOtherCosts() as $submittedCost) {
            if ($typeId == $submittedCost->getProfDeputyOtherCostTypeId()) {
                return $submittedCost;
            }
        }
        return null;
    }

    /**
     * Generates a static data array of submitted costs (values set in the database). Used in the summary view.
     */
    public function generateActualSubmittedOtherCosts(): array
    {
        $defaultOtherCosts = $this->getProfDeputyOtherCostTypeIds();
        $submittedCosts = [];
        foreach ($defaultOtherCosts as $defaultOtherCost) {
            $submittedCost = $this->getProfDeputyOtherCostByTypeId($defaultOtherCost['typeId']);
            $submittedCosts[$defaultOtherCost['typeId']]['typeId'] = $defaultOtherCost['typeId'];
            $submittedCosts[$defaultOtherCost['typeId']]['amount'] = !empty($submittedCost) ? $submittedCost->getAmount() : null;
            $submittedCosts[$defaultOtherCost['typeId']]['hasMoreDetails'] = $defaultOtherCost['hasMoreDetails'];
            $submittedCosts[$defaultOtherCost['typeId']]['moreDetails'] = !empty($submittedCost) ? $submittedCost->getMoreDetails() : '';
        }

        return $submittedCosts;
    }

    public function hasProfDeputyOtherCosts(): bool
    {
        return (bool) count($this->getProfDeputyOtherCosts() ? $this->getProfDeputyOtherCosts() : []) > 0;
    }
}
