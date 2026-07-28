<?php

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use OPG\Digideps\Frontend\Entity\Report\ProfDeputyEstimateCost;
use OPG\Digideps\Frontend\Entity\Report\Report;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

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

    #[JMS\Type('array')]
    #[JMS\Groups(['prof-deputy-estimate-costs'])]
    private array $profDeputyEstimateCostTypeIds = [];

    /**
     * @var float
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-estimate-management-costs'])]
    #[Assert\NotBlank(message: 'profDeputyEstimateCost.profDeputyManagementCostAmount.amount.notBlank', groups: ['prof-deputy-estimate-management-costs'])]
    private $profDeputyManagementCostAmount;

    #[JMS\Type('array')]
    #[JMS\Groups(['prof-deputy-estimate-costs'])]
    private array $profDeputyManagementCostTypeIds = [];

    /**
     * @var string yes/no
     */
    #[Assert\NotBlank(message: 'common.yesnochoice.notBlank', groups: ['prof-deputy-costs-estimate-more-info'])]
    #[JMS\Type('string')]
    #[JMS\Groups(['deputyCostsEstimateMoreInfo'])]
    private $profDeputyCostsEstimateHasMoreInfo;

    #[JMS\Type('string')]
    #[JMS\Groups(['deputyCostsEstimateMoreInfo'])]
    #[Assert\NotBlank(message: 'profDeputyCostsEstimateMoreInfo.details.notBlank', groups: ['prof-deputy-costs-estimate-more-info-details'])]
    private $profDeputyCostsEstimateMoreInfoDetails;

    /**
     * @return array
     */
    public function getProfDeputyEstimateCostTypeIds(): array
    {
        return $this->profDeputyEstimateCostTypeIds;
    }

    public function setProfDeputyEstimateCostTypeIds($profDeputyEstimateCostTypeIds): static
    {
        $this->profDeputyEstimateCostTypeIds = $profDeputyEstimateCostTypeIds;

        return $this;
    }

    public function getProfDeputyCostsEstimateHowCharged(): ?string
    {
        return $this->profDeputyCostsEstimateHowCharged;
    }

    /**
     * @param string $profDeputyCostsEstimateHowCharged
     */
    public function setProfDeputyCostsEstimateHowCharged($profDeputyCostsEstimateHowCharged): static
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

    /**
     * @param string $typeId
     */
    protected function getProfDeputyEstimateCostByTypeId($typeId): ?ProfDeputyEstimateCost
    {
        foreach ($this->getProfDeputyEstimateCosts() as $submittedCost) {
            if ($typeId == $submittedCost->getProfDeputyEstimateCostTypeId()) {
                return $submittedCost;
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function getProfDeputyCostsEstimateHasMoreInfo()
    {
        return $this->profDeputyCostsEstimateHasMoreInfo;
    }

    /**
     * @param string $profDeputyCostsEstimateHasMoreInfo
     */
    public function setProfDeputyCostsEstimateHasMoreInfo($profDeputyCostsEstimateHasMoreInfo): static
    {
        $this->profDeputyCostsEstimateHasMoreInfo = $profDeputyCostsEstimateHasMoreInfo;

        return $this;
    }

    public function getProfDeputyCostsEstimateMoreInfoDetails()
    {
        return $this->profDeputyCostsEstimateMoreInfoDetails;
    }

    public function setProfDeputyCostsEstimateMoreInfoDetails($profDeputyCostsEstimateMoreInfoDetails): static
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

    /**
     * @return float
     */
    public function getProfDeputyManagementCostAmount()
    {
        return $this->profDeputyManagementCostAmount;
    }

    /**
     * @param float $profDeputyManagementCostAmount
     */
    public function setProfDeputyManagementCostAmount($profDeputyManagementCostAmount): static
    {
        $this->profDeputyManagementCostAmount = $profDeputyManagementCostAmount;

        return $this;
    }

    /**
     * @return array
     */
    public function getProfDeputyManagementCostTypeIds(): array
    {
        return $this->profDeputyManagementCostTypeIds;
    }

    /**
     * @param array $profDeputyManagementCostTypeIds
     */
    public function setProfDeputyManagementCostTypeIds($profDeputyManagementCostTypeIds): static
    {
        $this->profDeputyManagementCostTypeIds = $profDeputyManagementCostTypeIds;

        return $this;
    }
}
