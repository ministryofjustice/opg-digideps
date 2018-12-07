<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\Fee;
use AppBundle\Entity\Report\ProfDeputyInterimCost;
use AppBundle\Entity\Report\ProfServiceFee;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportProfDeputyCostsTrait
{
    /**
     * @var boolean
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"prof-deputy-costs-how-charged"})
     * @ORM\Column(name="prof_dc_hc_fixed", type="boolean", nullable=true)
     */
    private $profDeputyCostsHowChargedFixed;

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"prof-deputy-costs-how-charged"})
     * @ORM\Column(name="prof_dc_hc_assessed", type="boolean", nullable=true)
     */
    private $profDeputyCostsHowChargedAssessed;

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"prof-deputy-costs-how-charged"})
     * @ORM\Column(name="prof_dc_hc_agreed", type="boolean", nullable=true)
     */
    private $profDeputyCostsHowChargedAgreed;


    /**
     * @var string yes/no
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report-prof-deputy-costs-prev"})
     * @ORM\Column(name="prof_dc_has_previous", type="string", length=3, nullable=true)
     */
    private $profDeputyCostsHasPrevious;

    /**
     * @JMS\Type("array<AppBundle\Entity\Report\ProfDeputyPreviousCost>")
     * @JMS\Groups({"report-prof-deputy-costs-prev"})
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\ProfDeputyPreviousCost", mappedBy="report", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $profDeputyPreviousCosts;


    /**
     * @var string yes/no
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report-prof-deputy-interim"})
     * @ORM\Column(name="prof_dc_has_interim", type="string", length=3, nullable=true)
     */
    private $profDeputyCostsHasInterim;

    /**
     * @JMS\Type("array<AppBundle\Entity\Report\ProfDeputyInterimCost>")
     * @JMS\Groups({"report-prof-deputy-costs-interim"})
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\ProfDeputyInterimCost", mappedBy="report", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $profDeputyInterimCosts;

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
     * @return string
     */
    public function getProfDeputyCostsHasPrevious()
    {
        return $this->profDeputyCostsHasPrevious;
    }

    /**
     * @param string $profDeputyCostsHasPrevious
     * @return ReportProfDeputyCostsTrait
     */
    public function setProfDeputyCostsHasPrevious($profDeputyCostsHasPrevious)
    {
        $this->profDeputyCostsHasPrevious = $profDeputyCostsHasPrevious;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProfDeputyPreviousCosts()
    {
        return $this->profDeputyPreviousCosts;
    }

    /**
     * @param mixed $profDeputyPreviousCosts
     */
    public function setProfDeputyPreviousCosts($profDeputyPreviousCosts)
    {
        $this->profDeputyPreviousCosts = $profDeputyPreviousCosts;
    }

    /**
     * @return string
     */
    public function getProfDeputyCostsHasInterim()
    {
        return $this->profDeputyCostsHasInterim;
    }

    /**
     * @param string $profDeputyCostsHasInterim
     */
    public function setProfDeputyCostsHasInterim($profDeputyCostsHasInterim)
    {
        $this->profDeputyCostsHasInterim = $profDeputyCostsHasInterim;
    }

    /**
     * @return mixed
     */
    public function getProfDeputyInterimCosts()
    {
        return $this->profDeputyInterimCosts;
    }

    /**
     * @param mixed $profDeputyInterimCosts
     * @return ReportProfDeputyCostsTrait
     */
    public function setProfDeputyInterimCosts($profDeputyInterimCosts)
    {
        $this->profDeputyInterimCosts = $profDeputyInterimCosts;
        return $this;
    }

    /**
     * @param ProfDeputyInterimCost $ic
     */
    public function addProfDeputyInterimCosts(ProfDeputyInterimCost $ic)
    {
        if (!$this->getProfDeputyInterimCosts()->contains($ic)) {
            $this->getProfDeputyInterimCosts()->add($ic);
        }
    }

}
