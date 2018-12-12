<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\ProfDeputyOtherCost;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Report\ProfDeputyPreviousCost;
use AppBundle\Entity\Report\ProfDeputyInterimCost;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
     * @var ProfDeputyOtherCost[]
     *
     * @JMS\Type("array<AppBundle\Entity\Report\ProfDeputyOtherCost>")
     * @JMS\Groups({"prof-deputy-other-costs"})
     */
    private $profDeputyOtherCosts;

    /**
     * @var array
     */
    private $profDeputyOtherCostIds;

    /**
     * @var ProfDeputyPreviousCost[]
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
     * @var ProfDeputyInterimCost[]
     * @JMS\Groups({"profDeputyInterimCosts"})
     * @JMS\Type("array<AppBundle\Entity\Report\ProfDeputyInterimCost>")
     */
    private $profDeputyInterimCosts = [];

    /**
     * @var float
     *
     * @Assert\NotBlank( message="profDeputyCostsScco.amountToScco.notBlank", groups={"prof-deputy-costs-scco"} )
     * @Assert\Range(min=0, minMessage = "profDeputyCostsScco.amountToScco.minMessage", groups={"prof-deputy-costs-scco"})
     * @JMS\Type("double")
     * @JMS\Groups({"profDeputyCostsScco"})
     */
    private $profDeputyCostsAmountToScco;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"profDeputyCostsScco"})
     */
    private $profDeputyCostsReasonBeyondEstimate;

    /**
     * return true if only fixed is true
     * @return boolean
     */
    public function hasProfDeputyCostsHowChargedFixedOnly()
    {
        return $this->profDeputyCostsHowChargedFixed
            && !$this->profDeputyCostsHowChargedAssessed
            && !$this->profDeputyCostsHowChargedAgreed;
    }

    /**
     * @JMS\Type("array")
     * @JMS\Groups({"prof-deputy-other-costs"})
     */
    private $profDeputyOtherCostTypeIds;

    /**
     * @return array
     */
    public function getProfDeputyOtherCostTypeIds()
    {
        return $this->profDeputyOtherCostTypeIds;
    }

    /**
     * @param $profDeputyOtherCostTypeIds
     * @return $this
     */
    public function setProfDeputyOtherCostTypeIds($profDeputyOtherCostTypeIds)
    {
        $this->profDeputyOtherCostTypeIds = $profDeputyOtherCostTypeIds;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getProfDeputyCostsHowChargedFixed()
    {
        return $this->profDeputyCostsHowChargedFixed;
    }

    /**
     * @param string $profDeputyCostsHowChargedFixed
     */
    public function setProfDeputyCostsHowChargedFixed($profDeputyCostsHowChargedFixed)
    {
        $this->profDeputyCostsHowChargedFixed = $profDeputyCostsHowChargedFixed;
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
     */
    public function setProfDeputyCostsHowChargedAssessed($profDeputyCostsHowChargedAssessed)
    {
        $this->profDeputyCostsHowChargedAssessed = $profDeputyCostsHowChargedAssessed;
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
     */
    public function setProfDeputyCostsHowChargedAgreed($profDeputyCostsHowChargedAgreed)
    {
        $this->profDeputyCostsHowChargedAgreed = $profDeputyCostsHowChargedAgreed;
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function profCostsHowChangedAtLeastOne(ExecutionContextInterface $context)
    {
        if (!$this->profDeputyCostsHowChargedFixed
            && !$this->profDeputyCostsHowChargedAssessed
            && ! $this->profDeputyCostsHowChargedAgreed
        ) {
            $context->buildViolation('profDeputyHowChanged.atLeastOne')->atPath('profDeputyCostsHowChargedFixed')->addViolation();
        }
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
     */
    public function setProfDeputyCostsHasPrevious($profDeputyCostsHasPrevious)
    {
        $this->profDeputyCostsHasPrevious = $profDeputyCostsHasPrevious;
    }

    /**
     * @return ProfDeputyPreviousCost[]
     */
    public function getProfDeputyPreviousCosts()
    {
        return $this->profDeputyPreviousCosts;
    }

    /**
     * @param ProfDeputyPreviousCost[] $profDeputyPreviousCosts
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
     * @return ProfDeputyInterimCost[]
     */
    public function getProfDeputyInterimCosts()
    {
        return $this->profDeputyInterimCosts;
    }

    /**
     * @param ProfDeputyInterimCost[] $profDeputyInterimCosts
     */
    public function setProfDeputyInterimCosts($profDeputyInterimCosts)
    {
        $this->profDeputyInterimCosts = $profDeputyInterimCosts;
    }

    /**
     *
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
     * @param ProfDeputyInterimCost $ic
     */
    public function addProfDeputyInterimCosts(ProfDeputyInterimCost $ic)
    {
        $this->profDeputyInterimCosts[] = $ic;
    }

    /**
     * @return float
     */
    public function getProfDeputyCostsAmountToScco()
    {
        return $this->profDeputyCostsAmountToScco;
    }

    /**
     * @param float $profDeputyCostsAmountToScco
     */
    public function setProfDeputyCostsAmountToScco($profDeputyCostsAmountToScco)
    {
        $this->profDeputyCostsAmountToScco = $profDeputyCostsAmountToScco;
    }

    /**
     * @return string
     */
    public function getProfDeputyCostsReasonBeyondEstimate()
    {
        return $this->profDeputyCostsReasonBeyondEstimate;
    }

    /**
     * @param string $profDeputyCostsReasonBeyondEstimate
     */
    public function setProfDeputyCostsReasonBeyondEstimate($profDeputyCostsReasonBeyondEstimate)
    {
        $this->profDeputyCostsReasonBeyondEstimate = $profDeputyCostsReasonBeyondEstimate;
    }

    /**
     * @return array
     */
    public function getProfDeputyOtherCostIds()
    {
        return $this->profDeputyOtherCostIds;
    }

    /**
     * @param array $profDeputyOtherCostIds
     */
    public function setProfDeputyOtherCostIds($profDeputyOtherCostIds)
    {
        $this->profDeputyOtherCostIds = $profDeputyOtherCostIds;
    }
}
