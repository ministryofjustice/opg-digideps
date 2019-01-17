<?php

namespace AppBundle\Entity\Report\Traits;

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



}
