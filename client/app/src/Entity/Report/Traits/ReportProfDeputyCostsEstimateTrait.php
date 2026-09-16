<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\ProfDeputyEstimateCost;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @phpstan-type CostTypeIds array<array{typeId: string, hasMoreDetails: bool}>
 */
trait ReportProfDeputyCostsEstimateTrait
{
    #[JMS\Type('string')]
    #[JMS\Groups(['deputyCostsEstimateHowCharged'])]
    #[Assert\NotBlank(message: 'profDeputyEstimateCost.profDeputyCostsEstimateHowCharged.notBlank', groups: ['prof-deputy-costs-estimate-how-charged'])]
    private ?string $profDeputyCostsEstimateHowCharged = null;

    /**
     * @var ProfDeputyEstimateCost[]
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\ProfDeputyEstimateCost>')]
    #[JMS\Groups(['prof-deputy-estimate-costs'])]
    private array $profDeputyEstimateCosts = [];

    /**
     * @var CostTypeIds
     */
    #[JMS\Type('array')]
    #[JMS\Groups(['prof-deputy-estimate-costs'])]
    private array $profDeputyEstimateCostTypeIds = [];

    #[JMS\Type('double')]
    #[JMS\Groups(['prof-deputy-estimate-management-costs'])]
    #[Assert\NotBlank(message: 'profDeputyEstimateCost.profDeputyManagementCostAmount.amount.notBlank', groups: ['prof-deputy-estimate-management-costs'])]
    private ?float $profDeputyManagementCostAmount = null;

    /**
     * 'yes'|'no'|null
     */
    #[Assert\NotBlank(message: 'common.yesnochoice.notBlank', groups: ['prof-deputy-costs-estimate-more-info'])]
    #[JMS\Type('string')]
    #[JMS\Groups(['deputyCostsEstimateMoreInfo'])]
    private ?string $profDeputyCostsEstimateHasMoreInfo = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['deputyCostsEstimateMoreInfo'])]
    #[Assert\NotBlank(message: 'profDeputyCostsEstimateMoreInfo.details.notBlank', groups: ['prof-deputy-costs-estimate-more-info-details'])]
    private ?string $profDeputyCostsEstimateMoreInfoDetails = null;

    /**
     * @return CostTypeIds
     */
    public function getProfDeputyEstimateCostTypeIds(): array
    {
        return $this->profDeputyEstimateCostTypeIds;
    }

    /**
     * @param CostTypeIds $profDeputyEstimateCostTypeIds
     */
    public function setProfDeputyEstimateCostTypeIds(array $profDeputyEstimateCostTypeIds): static
    {
        $this->profDeputyEstimateCostTypeIds = $profDeputyEstimateCostTypeIds;

        return $this;
    }

    public function getProfDeputyCostsEstimateHowCharged(): ?string
    {
        return $this->profDeputyCostsEstimateHowCharged;
    }

    public function setProfDeputyCostsEstimateHowCharged(?string $profDeputyCostsEstimateHowCharged): static
    {
        $this->profDeputyCostsEstimateHowCharged = $profDeputyCostsEstimateHowCharged;

        return $this;
    }

    /**
     * return true if only fixed is true.
     */
    public function hasProfDeputyCostsEstimateHowChargedFixedOnly(): bool
    {
        $getProfDeputyCostsEstimateHowChargedLower = is_null($this->getProfDeputyCostsEstimateHowCharged()) ? '' : strtolower($this->getProfDeputyCostsEstimateHowCharged());

        return $getProfDeputyCostsEstimateHowChargedLower == Report::PROF_DEPUTY_COSTS_TYPE_FIXED;
    }

    /**
     * @return ProfDeputyEstimateCost[]
     */
    public function getProfDeputyEstimateCosts(): array
    {
        return $this->profDeputyEstimateCosts;
    }

    /**
     * @param ProfDeputyEstimateCost[] $profDeputyEstimateCosts
     */
    public function setProfDeputyEstimateCosts(array $profDeputyEstimateCosts): static
    {
        $this->profDeputyEstimateCosts = $profDeputyEstimateCosts;

        return $this;
    }

    protected function getProfDeputyEstimateCostByTypeId(string $typeId): ?ProfDeputyEstimateCost
    {
        return array_find(
            $this->getProfDeputyEstimateCosts(),
            fn (ProfDeputyEstimateCost $submittedCost) => $typeId == $submittedCost->getProfDeputyEstimateCostTypeId()
        );

    }

    public function getProfDeputyCostsEstimateHasMoreInfo(): ?string
    {
        return $this->profDeputyCostsEstimateHasMoreInfo;
    }

    public function setProfDeputyCostsEstimateHasMoreInfo(?string $profDeputyCostsEstimateHasMoreInfo): static
    {
        $this->profDeputyCostsEstimateHasMoreInfo = $profDeputyCostsEstimateHasMoreInfo;

        return $this;
    }

    public function getProfDeputyCostsEstimateMoreInfoDetails(): ?string
    {
        return $this->profDeputyCostsEstimateMoreInfoDetails;
    }

    public function setProfDeputyCostsEstimateMoreInfoDetails(?string $profDeputyCostsEstimateMoreInfoDetails): static
    {
        $this->profDeputyCostsEstimateMoreInfoDetails = $profDeputyCostsEstimateMoreInfoDetails;

        return $this;
    }

    /**
     * Generates a static data array of submitted costs (values set in the database). Used in the summary view.
     *
     * @return array
     */
    public function generateActualSubmittedEstimateCosts(): array
    {
        $defaultEstimateCosts = $this->getProfDeputyEstimateCostTypeIds();
        $submittedCosts = [];
        foreach ($defaultEstimateCosts as $defaultEstimateCost) {
            $submittedCost = $this->getProfDeputyEstimateCostByTypeId($defaultEstimateCost['typeId']);
            $submittedCosts[$defaultEstimateCost['typeId']]['typeId'] = $defaultEstimateCost['typeId'];
            $submittedCosts[$defaultEstimateCost['typeId']]['amount'] = !empty($submittedCost) ? $submittedCost->getAmount() : null;
            $submittedCosts[$defaultEstimateCost['typeId']]['hasMoreDetails'] = $defaultEstimateCost['hasMoreDetails'];
            $submittedCosts[$defaultEstimateCost['typeId']]['moreDetails'] = !empty($submittedCost) ? $submittedCost->getMoreDetails() : '';
        }

        return $submittedCosts;
    }

    public function getProfDeputyManagementCostAmount(): ?float
    {
        return $this->profDeputyManagementCostAmount;
    }

    public function setProfDeputyManagementCostAmount(?float $profDeputyManagementCostAmount): static
    {
        $this->profDeputyManagementCostAmount = $profDeputyManagementCostAmount;

        return $this;
    }
}
