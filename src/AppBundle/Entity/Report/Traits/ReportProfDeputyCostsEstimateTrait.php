<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\ProfDeputyEstimateCost;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @var ArrayCollection
     *
     * @JMS\Type("array<AppBundle\Entity\Report\ProfDeputyEstimateCost>")
     * @JMS\Groups({"prof-deputy-estimate-costs"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\ProfDeputyEstimateCost", mappedBy="report", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $profDeputyEstimateCosts;

    /**
     * @var string
     * @JMS\Groups({"prof-deputy-costs-estimate-more-info"})
     * @ORM\Column(name="prof_dc_estimate_more_info", type="string", length=3, nullable=true)
     */
    private $profDeputyCostsEstimateHasMoreInfo;

    /**
     * @var string
     * @JMS\Groups({"prof-deputy-costs-estimate-more-info"})
     * @ORM\Column(name="prof_dc_estimate_more_info_details", type="text", nullable=true)
     */
    private $profDeputyCostsEstimateMoreInfoDetails;

    /**
     * @var float
     * @JMS\Type("string")
     * @JMS\Groups({"prof-deputy-estimate-management-costs"})
     * @JMS\SerializedName("prof_deputy_management_cost_amount")
     * @ORM\Column(name="prof_dc_estimate_management_cost", type="float", precision=14, scale=2, nullable=true)
     */
    private $profDeputyCostsEstimateManagementCostAmount;

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
     * @return mixed
     */
    public function getProfDeputyEstimateCosts()
    {
        return $this->profDeputyEstimateCosts;
    }

    /**
     * @param ArrayCollection $collection
     * @return ReportProfDeputyCostsEstimateTrait
     */
    public function setProfDeputyEstimateCosts(ArrayCollection $collection)
    {
        $this->profDeputyEstimateCosts = $collection;

        return $this;
    }

    /**
     * @param ProfDeputyEstimateCost $profDeputyEstimateCost
     * @return $this
     */
    public function addProfDeputyEstimateCost(ProfDeputyEstimateCost $profDeputyEstimateCost)
    {
        $this->profDeputyEstimateCosts->add($profDeputyEstimateCost);

        return $this;
    }

    /**
     * @param string $typeId
     *
     * @return ProfDeputyEstimateCost
     */
    public function getProfDeputyEstimateCostByTypeId($typeId)
    {
        return $this->getProfDeputyEstimateCosts()->filter(
            function (ProfDeputyEstimateCost $profDeputyEstimateCost) use ($typeId) {
                return $profDeputyEstimateCost->getProfDeputyEstimateCostTypeId() == $typeId;
            }
        )->first();
    }

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
     * @JMS\VirtualProperty()
     * @JMS\Groups({"prof-deputy-estimate-costs"})
     */
    public function getProfDeputyEstimateCostsTotal()
    {
        $total = 0;

        foreach ($this->getProfDeputyEstimateCosts() as $profDeputyEstimateCost) {
            $total += $profDeputyEstimateCost->getAmount();
        }

        $total += $this->getProfDeputyCostsEstimateManagementCostAmount();

        return $total;
    }

    /**
     * @return float
     * @JMS\Groups({"prof-deputy-estimate-management-costs"})
     * @JMS\SerializedName("prof_deputy_management_cost_amount")
     * @JMS\Type("double")
     */
    public function getProfDeputyCostsEstimateManagementCostAmount()
    {
        return $this->profDeputyCostsEstimateManagementCostAmount;
    }

    /**
     * @param float $profDeputyCostsEstimateManagementCostAmount
     * @return ReportProfDeputyCostsEstimateTrait
     */
    public function setProfDeputyCostsEstimateManagementCostAmount($profDeputyCostsEstimateManagementCostAmount)
    {
        $this->profDeputyCostsEstimateManagementCostAmount = $profDeputyCostsEstimateManagementCostAmount;

        return $this;
    }


}
