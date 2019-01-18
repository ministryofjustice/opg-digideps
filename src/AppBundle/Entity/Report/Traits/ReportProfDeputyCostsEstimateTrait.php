<?php

namespace AppBundle\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportProfDeputyCostsEstimateTrait
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"prof-deputy-costs-estimate-how-charged"})
     * @ORM\Column(name="prof_dc_estimate_hc", type="string", length=10, nullable=true)
     */
    private $profDeputyCostsEstimateHowCharged;

    /**
     * @JMS\Type("array<AppBundle\Entity\Report\ProfDeputyEstimateCost>")
     * @JMS\Groups({"prof-deputy-estimate-costs"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\ProfDeputyEstimateCost", mappedBy="report", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $profDeputyEstimateCosts;

    /**
     * Hold prof deputy estimate costs type
     * 1st value = id, 2nd value = hasMoreInformation.
     *
     * @JMS\Groups({"prof-deputy-estimate-costs"})
     *
     * @var array
     */
    public static $profDeputyEstimateCostTypeIds = [
        ['typeId' => 'contact-client', 'hasMoreDetails' => false],
        ['typeId' => 'contact-case-manager-carers', 'hasMoreDetails' => false],
        ['typeId' => 'contact-others', 'hasMoreDetails' => false],
        ['typeId' => 'forms-documents', 'hasMoreDetails' => false],
        ['typeId' => 'other', 'hasMoreDetails' => true],
    ];

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("prof_deputy_estimate_cost_type_ids")
     * @JMS\Type("array")
     * @JMS\Groups({"prof-deputy-estimate-costs"})
     *
     * @return array
     */
    public static function getProfDeputyEstimateCostTypeIds()
    {
        return self::$profDeputyEstimateCostTypeIds;
    }

    /**
     * @param $profDeputyEstimateCostTypeIds
     * @return $this
     */
    public static function setProfDeputyEstimateCostTypeIds($profDeputyEstimateCostTypeIds)
    {
        self::$profDeputyEstimateCostTypeIds = $profDeputyEstimateCostTypeIds;
    }

    /**
     * @return string
     */
    public function getProfDeputyCostsEstimateHowCharged()
    {
        return $this->profDeputyCostsEstimateHowCharged;
    }

    /**
     * @param $profDeputyCostsEstimateHowCharged string
     * @return $this
     */
    public function setProfDeputyCostsEstimateHowCharged($profDeputyCostsEstimateHowCharged)
    {
        $this->profDeputyCostsEstimateHowCharged = $profDeputyCostsEstimateHowCharged;
        return $this;
    }


}
