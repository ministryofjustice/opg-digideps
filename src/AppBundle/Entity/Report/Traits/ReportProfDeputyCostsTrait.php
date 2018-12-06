<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\Fee;
use AppBundle\Entity\Report\ProfServiceFee;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportProfDeputyCostsTrait
{
    /**
     * @var boolean
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"deputyCostsHowCharged"})
     */
    private $profDeputyCostsHowChargedFixed;

    /**
     * @var boolean
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"deputyCostsHowCharged"})
     */
    private $profDeputyCostsHowChargedAssessed;

    /**
     * @var boolean
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"deputyCostsHowCharged"})
     */
    private $profDeputyCostsHowChargedAgreed;

    /**
     * @var string yes/no
     *
     * @JMS\Type("string")
     * @JMS\Groups({"profDeputyCostsHasPrevious"})
     */
    private $profDeputyCostsHasPrevious;

    /**
     * @var AppBundle\Entity\Report\ProfDeputyPreviousCost[]
     *
     * @JMS\Type("array<AppBundle\Entity\Report\ProfDeputyPreviousCost>")
     */
    private $profDeputyPreviousCosts;


    /**
     * @var string yes/no
     *
     * @JMS\Type("string")
     * @JMS\Groups({"profDeputyCostsHasInterim"})
     */
    private $profDeputyCostsHasInterim;

    /**
     * @var AppBundle\Entity\Report\ProfDeputyInterimCost[]
     *
     * @JMS\Type("array<AppBundle\Entity\Report\ProfDeputyInterimCost>")
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
     * @return AppBundle\Entity\Report\ProfDeputyPreviousCost[]
     */
    public function getProfDeputyPreviousCosts()
    {
        return $this->profDeputyPreviousCosts;
    }

    /**
     * @param AppBundle\Entity\Report\ProfDeputyPreviousCost[] $profDeputyPreviousCosts
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
     * @return ReportProfDeputyCostsTrait
     */
    public function setProfDeputyCostsHasInterim($profDeputyCostsHasInterim)
    {
        $this->profDeputyCostsHasInterim = $profDeputyCostsHasInterim;
        return $this;
    }

    /**
     * @return AppBundle\Entity\Report\ProfDeputyInterimCost[]
     */
    public function getProfDeputyInterimCosts()
    {
        return $this->profDeputyInterimCosts;
    }

    /**
     * @param AppBundle\Entity\Report\ProfDeputyInterimCost[] $profDeputyInterimCosts
     * @return ReportProfDeputyCostsTrait
     */
    public function setProfDeputyInterimCosts($profDeputyInterimCosts)
    {
        $this->profDeputyInterimCosts = $profDeputyInterimCosts;
        return $this;
    }





}
