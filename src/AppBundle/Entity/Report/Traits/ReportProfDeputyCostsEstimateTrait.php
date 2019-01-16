<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\ProfDeputyOtherCost;
use AppBundle\Entity\Report\Report;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Report\ProfDeputyPreviousCost;
use AppBundle\Entity\Report\ProfDeputyInterimCost;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait ReportProfDeputyCostsEstimateTrait
{
    /**
     * @var string
     *
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
     * @param ProfDeputyEstimateCosts[] $profDeputyEstimateCosts
     *
     * @return $this
     */
    public function setProfDeputyEstimateCosts($profDeputyEstimateCosts)
    {
        $this->profDeputyEstimateCosts = $profDeputyEstimateCosts;
        return $this;
    }

}
