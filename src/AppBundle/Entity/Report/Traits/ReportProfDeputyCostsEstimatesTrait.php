<?php

namespace AppBundle\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportProfDeputyCostsEstimatesTrait
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"prof-deputy-costs-estimates-how-charged"})
     * @ORM\Column(name="prof_dc_estimates_hc", type="string", length=10, nullable=true)
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


}
