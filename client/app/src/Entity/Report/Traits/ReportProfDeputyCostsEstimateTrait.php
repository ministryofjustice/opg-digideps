<?php

namespace App\Entity\Report\Traits;

use App\Entity\Report\ProfDeputyEstimateCost;
use App\Entity\Report\Report;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportProfDeputyCostsEstimateTrait
{
    /**
     * @var string
     *
     * @Assert\NotBlank( message="profDeputyEstimateCost.profDeputyCostsEstimateHowCharged.notBlank", groups={"prof-deputy-costs-estimate-how-charged"} )
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"deputyCostsEstimateHowCharged"})
     */
    private $profDeputyCostsEstimateHowCharged;

    /**
     * @var ProfDeputyEstimateCost[]
     *
     * @JMS\Type("array<App\Entity\Report\ProfDeputyEstimateCost>")
     *
     * @JMS\Groups({"prof-deputy-estimate-costs"})
     */
    private $profDeputyEstimateCosts = [];

    /**
     * @JMS\Type("array")
     *
     * @JMS\Groups({"prof-deputy-estimate-costs"})
     */
    private $profDeputyEstimateCostTypeIds = [];

    /**
     * @var float
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"prof-deputy-estimate-management-costs"})
     *
     * @Assert\NotBlank( message="profDeputyEstimateCost.profDeputyManagementCostAmount.amount.notBlank", groups={"prof-deputy-estimate-management-costs"} )
     */
    private $profDeputyManagementCostAmount;

    /**
     * @JMS\Type("array")
     *
     * @JMS\Groups({"prof-deputy-estimate-costs"})
     */
    private $profDeputyManagementCostTypeIds = [];

    /**
     * @var string yes/no
     *
     * @Assert\NotBlank(message="common.yesnochoice.notBlank", groups={"prof-deputy-costs-estimate-more-info"})
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"deputyCostsEstimateMoreInfo"})
     */
    private $profDeputyCostsEstimateHasMoreInfo;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"deputyCostsEstimateMoreInfo"})
     *
     * @Assert\NotBlank(message="profDeputyCostsEstimateMoreInfo.details.notBlank", groups={"prof-deputy-costs-estimate-more-info-details"})
     */
    private $profDeputyCostsEstimateMoreInfoDetails;

    /**
     * @return array
     */
    public function getProfDeputyEstimateCostTypeIds()
    {
        return $this->profDeputyEstimateCostTypeIds;
    }

    /**
     * @return $this
     */
    public function setProfDeputyEstimateCostTypeIds($profDeputyEstimateCostTypeIds)
    {
        $this->profDeputyEstimateCostTypeIds = $profDeputyEstimateCostTypeIds;

        return $this;
    }

    /**
     * @return string
     */
    public function getProfDeputyCostsEstimateHowCharged()
    {
        return $this->profDeputyCostsEstimateHowCharged;
    }

    /**
     * @param string $profDeputyCostsEstimateHowCharged
     *
     * @return $this
     */
    public function setProfDeputyCostsEstimateHowCharged($profDeputyCostsEstimateHowCharged)
    {
        $this->profDeputyCostsEstimateHowCharged = $profDeputyCostsEstimateHowCharged;

        return $this;
    }

    /**
     * return true if only fixed is true.
     *
     * @return bool
     */
    public function hasProfDeputyCostsEstimateHowChargedFixedOnly()
    {
        $getProfDeputyCostsEstimateHowChargedLower = null !== $this->getProfDeputyCostsEstimateHowCharged() ? strtolower($this->getProfDeputyCostsEstimateHowCharged()) : '';

        return Report::PROF_DEPUTY_COSTS_TYPE_FIXED == $getProfDeputyCostsEstimateHowChargedLower;
    }

    /**
     * @return ProfDeputyEstimateCost[]
     */
    public function getProfDeputyEstimateCosts()
    {
        return $this->profDeputyEstimateCosts;
    }

    /**
     * @param ProfDeputyEstimateCost[] $profDeputyEstimateCosts
     *
     * @return $this
     */
    public function setProfDeputyEstimateCosts($profDeputyEstimateCosts)
    {
        $this->profDeputyEstimateCosts = $profDeputyEstimateCosts;

        return $this;
    }

    /**
     * @param string $typeId
     *
     * @return ProfDeputyEstimateCost
     */
    protected function getProfDeputyEstimateCostByTypeId($typeId)
    {
        foreach ($this->getProfDeputyEstimateCosts() as $submittedCost) {
            if ($typeId == $submittedCost->getProfDeputyEstimateCostTypeId()) {
                return $submittedCost;
            }
        }
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
     *
     * @return ReportProfDeputyCostsEstimateTrait
     */
    public function setProfDeputyCostsEstimateHasMoreInfo($profDeputyCostsEstimateHasMoreInfo)
    {
        $this->profDeputyCostsEstimateHasMoreInfo = $profDeputyCostsEstimateHasMoreInfo;

        return $this;
    }

    public function getProfDeputyCostsEstimateMoreInfoDetails()
    {
        return $this->profDeputyCostsEstimateMoreInfoDetails;
    }

    /**
     * @return ReportProfDeputyCostsEstimateTrait
     */
    public function setProfDeputyCostsEstimateMoreInfoDetails($profDeputyCostsEstimateMoreInfoDetails)
    {
        $this->profDeputyCostsEstimateMoreInfoDetails = $profDeputyCostsEstimateMoreInfoDetails;

        return $this;
    }

    /**
     * Generates a static data array of submitted costs (values set in the database). Used in the summary view.
     *
     * @return array
     */
    public function generateActualSubmittedEstimateCosts()
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
     *
     * @return ReportProfDeputyCostsEstimateTrait
     */
    public function setProfDeputyManagementCostAmount($profDeputyManagementCostAmount)
    {
        $this->profDeputyManagementCostAmount = $profDeputyManagementCostAmount;

        return $this;
    }

    /**
     * @return array
     */
    public function getProfDeputyManagementCostTypeIds()
    {
        return $this->profDeputyManagementCostTypeIds;
    }

    /**
     * @param array $profDeputyManagementCostTypeIds
     *
     * @return ReportProfDeputyCostsEstimateTrait
     */
    public function setProfDeputyManagementCostTypeIds($profDeputyManagementCostTypeIds)
    {
        $this->profDeputyManagementCostTypeIds = $profDeputyManagementCostTypeIds;

        return $this;
    }
}
