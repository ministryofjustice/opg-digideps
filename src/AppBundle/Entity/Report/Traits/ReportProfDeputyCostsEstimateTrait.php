<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\ProfDeputyEstimateCost;
use AppBundle\Entity\Report\ProfDeputyManagementCost;
use AppBundle\Entity\Report\Report;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportProfDeputyCostsEstimateTrait
{
    /**
     * @var string
     *
     * @Assert\NotBlank( message="profDeputyCostsEstimateHowCharged.howCharged.notBlank", groups={"prof-deputy-costs-estimate-how-charged"} )
     * @JMS\Type("string")
     * @JMS\Groups({"deputyCostsEstimateHowCharged"})
     */
    private $profDeputyCostsEstimateHowCharged;

    /**
     * @var ProfDeputyEstimateCost[]
     *
     * @JMS\Type("array<AppBundle\Entity\Report\ProfDeputyEstimateCost>")
     * @JMS\Groups({"prof-deputy-estimate-costs"})
     */
    private $profDeputyEstimateCosts;

    /**
     * @JMS\Type("array")
     * @JMS\Groups({"prof-deputy-estimate-costs"})
     */
    private $profDeputyEstimateCostTypeIds;

    /**
     * @var ProfDeputyManagementCost[]
     *
     * @JMS\Type("array<AppBundle\Entity\Report\ProfDeputyManagementCost>")
     * @JMS\Groups({"prof-deputy-management-costs"})
     */
    private $profDeputyManagementCosts;

    /**
     * @JMS\Type("array")
     * @JMS\Groups({"prof-deputy-management-costs"})
     */
    private $profDeputyManagementCostTypeIds;

    /**
     * @var string yes/no
     *
     * @Assert\NotBlank(message="common.yesnochoice.notBlank", groups={"prof-deputy-costs-estimate-more-info"})
     * @JMS\Type("string")
     * @JMS\Groups({"deputyCostsEstimateMoreInfo"})
     */
    private $profDeputyCostsEstimateHasMoreInfo;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"deputyCostsEstimateMoreInfo"})
     * @Assert\NotBlank(message="profDeputyCostsEstimateMoreInfo.details.notBlank", groups={"prof-deputy-costs-estimate-more-info-details"})
     */
    private $profDeputyCostsEstimateMoreInfoDetails;

    /**
     * @var float
     *
     * @JMS\Type("double")
     */
    private $profDeputyEstimateCostsTotal;

    /**
     * @return array
     */
    public function getProfDeputyEstimateCostTypeIds()
    {
        return $this->profDeputyEstimateCostTypeIds;
    }

    /**
     * @param $profDeputyEstimateCostTypeIds
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
     * @return $this
     */
    public function setProfDeputyCostsEstimateHowCharged($profDeputyCostsEstimateHowCharged)
    {
        $this->profDeputyCostsEstimateHowCharged = $profDeputyCostsEstimateHowCharged;

        return $this;
    }

    /**
     * return true if only fixed is true
     * @return boolean
     */
    public function hasProfDeputyCostsEstimateHowChargedFixedOnly()
    {
        return strtolower($this->getProfDeputyCostsEstimateHowCharged()) == Report::PROF_DEPUTY_COSTS_ESTIMATE_TYPE_FIXED;
    }

    /**
     * @return ProfDeputyEstimateCost[]
     */
    public function getProfDeputyEstimateCosts()
    {
        return $this->profDeputyEstimateCosts;
    }

    /**
     * @return ProfDeputyManagementCost[]
     */
    public function getProfDeputyManagementCosts()
    {
        return $this->profDeputyManagementCosts;
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
    private function getProfDeputyEstimateCostByTypeId($typeId)
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
     * @return ReportProfDeputyCostsEstimateTrait
     */
    public function setProfDeputyCostsEstimateHasMoreInfo($profDeputyCostsEstimateHasMoreInfo)
    {
        $this->profDeputyCostsEstimateHasMoreInfo = $profDeputyCostsEstimateHasMoreInfo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProfDeputyCostsEstimateMoreInfoDetails()
    {
        return $this->profDeputyCostsEstimateMoreInfoDetails;
    }

    /**
     * @param mixed $profDeputyCostsEstimateMoreInfoDetails
     * @return ReportProfDeputyCostsEstimateTrait
     */
    public function setProfDeputyCostsEstimateMoreInfoDetails($profDeputyCostsEstimateMoreInfoDetails)
    {
        $this->profDeputyCostsEstimateMoreInfoDetails = $profDeputyCostsEstimateMoreInfoDetails;

        return $this;
    }

    /**
     * @return float
     */
    public function getProfDeputyEstimateCostsTotal()
    {
        return $this->profDeputyEstimateCostsTotal;
    }

    /**
     * @param float $profDeputyEstimateCostsTotal
     */
    public function setProfDeputyEstimateCostsTotal($profDeputyEstimateCostsTotal)
    {
        $this->profDeputyEstimateCostsTotal = $profDeputyEstimateCostsTotal;
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
     * @return array
     */
    public function getProfDeputyManagementCostTypeIds()
    {
        return $this->profDeputyManagementCostTypeIds;
    }

    /**
     * @param array $profDeputyManagementCostTypeIds
     * @return ReportProfDeputyCostsEstimateTrait
     */
    public function setProfDeputyManagementCostTypeIds($profDeputyManagementCostTypeIds)
    {
        $this->profDeputyManagementCostTypeIds = $profDeputyManagementCostTypeIds;
        return $this;
    }

    /**
     * @param ProfDeputyManagementCost[] $profDeputyManagementCosts
     */
    public function setProfDeputyManagementCosts($profDeputyManagementCosts)
    {
        $this->profDeputyManagementCosts = $profDeputyManagementCosts;
    }
}
