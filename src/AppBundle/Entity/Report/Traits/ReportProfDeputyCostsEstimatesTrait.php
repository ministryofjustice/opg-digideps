<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\ProfDeputyOtherCost;
use AppBundle\Entity\Report\Report;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Report\ProfDeputyPreviousCost;
use AppBundle\Entity\Report\ProfDeputyInterimCost;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait ReportProfDeputyCostsEstimatesTrait
{

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"deputyCostsEstimatesHowCharged"})
     */
    private $profDeputyCostsEstimatesHowCharged;

    /**
     * @return string
     */
    public function getProfDeputyCostsEstimatesHowCharged()
    {
        return $this->profDeputyCostsEstimatesHowCharged;
    }

    /**
     * @param $profDeputyCostsEstimatesHowCharged string
     * @return $this
     */
    public function setProfDeputyCostsEstimatesHowCharged($profDeputyCostsEstimatesHowCharged)
    {
        $this->profDeputyCostsEstimatesHowCharged = $profDeputyCostsEstimatesHowCharged;
        return $this;
    }

    /**
     * return true if only fixed is true
     * @return boolean
     */
    public function hasProfDeputyCostsEstimatesHowChargedFixedOnly()
    {
        return strtolower($this->getProfDeputyCostsEstimatesHowCharged()) == Report::PROF_DEPUTY_ESTIMATE_COSTS_TYPE_FIXED;
    }



}
