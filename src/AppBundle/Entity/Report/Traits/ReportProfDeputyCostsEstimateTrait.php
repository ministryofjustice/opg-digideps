<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\ProfDeputyEstimateCost;
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
}
