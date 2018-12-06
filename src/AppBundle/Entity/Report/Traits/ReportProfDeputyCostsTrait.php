<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\Fee;
use AppBundle\Entity\Report\ProfDeputyOtherCost;
use AppBundle\Entity\Report\ProfServiceFee;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportProfDeputyCostsTrait
{
    /**
     * @var boolean
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"deputy-costs-how-charged"})
     * @ORM\Column(name="prof_dc_hc_fixed", type="boolean", nullable=true)
     */
    private $profDeputyCostsHowChargedFixed;

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"deputy-costs-how-charged"})
     * @ORM\Column(name="prof_dc_hc_assessed", type="boolean", nullable=true)
     */
    private $profDeputyCostsHowChargedAssessed;

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"deputy-costs-how-charged"})
     * @ORM\Column(name="prof_dc_hc_agreed", type="boolean", nullable=true)
     */
    private $profDeputyCostsHowChargedAgreed;

    /**
     * @var ProfDeputyOtherCost[]
     *
     * @JMS\Groups({"prof-deputy-other-costs"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\ProfDeputyOtherCost", mappedBy="report", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $profDeputyOtherCosts;

    /**
     * @return boolean
     */
    public function getProfDeputyCostsHowChargedFixed()
    {
        return $this->profDeputyCostsHowChargedFixed;
    }

    /**
     * @param string $profDeputyCostsHowChargedFixed
     * @return ReportProfDeputyCostsTrait
     */
    public function setProfDeputyCostsHowChargedFixed($profDeputyCostsHowChargedFixed)
    {
        $this->profDeputyCostsHowChargedFixed = $profDeputyCostsHowChargedFixed;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getProfDeputyCostsHowChargedAssessed()
    {
        return $this->profDeputyCostsHowChargedAssessed;
    }

    /**
     * @param string $profDeputyCostsHowChargedAssessed
     * @return ReportProfDeputyCostsTrait
     */
    public function setProfDeputyCostsHowChargedAssessed($profDeputyCostsHowChargedAssessed)
    {
        $this->profDeputyCostsHowChargedAssessed = $profDeputyCostsHowChargedAssessed;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getProfDeputyCostsHowChargedAgreed()
    {
        return $this->profDeputyCostsHowChargedAgreed;
    }

    /**
     * @param string $profDeputyCostsHowChargedAgreed
     * @return ReportProfDeputyCostsTrait
     */
    public function setProfDeputyCostsHowChargedAgreed($profDeputyCostsHowChargedAgreed)
    {
        $this->profDeputyCostsHowChargedAgreed = $profDeputyCostsHowChargedAgreed;
        return $this;
    }


    /**
     * @return ProfDeputyOtherCost[]
     */
    public function getProfDeputyOtherCosts()
    {
        return $this->profDeputyOtherCosts;
    }

    /**
     * @param $profDeputyOtherCosts
     * @return $this
     */
    public function setProfDeputyOtherCosts($profDeputyOtherCosts)
    {
        $this->profDeputyOtherCosts = $profDeputyOtherCosts;
        return $this;
    }

    /**
     * @param ProfDeputyOtherCost $profDeputyOtherCost
     * @return $this
     */
    public function addProfDeputyOtherCost(ProfDeputyOtherCost $profDeputyOtherCost)
    {
        if (!$this->profDeputyOtherCosts->contains($profDeputyOtherCost)) {
            $this->profDeputyOtherCosts->add($profDeputyOtherCost);
        }

        return $this;
    }
}
