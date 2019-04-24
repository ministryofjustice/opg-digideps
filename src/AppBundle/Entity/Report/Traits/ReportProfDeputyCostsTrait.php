<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\ProfDeputyOtherCost;
use AppBundle\Entity\Report\Report;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Report\ProfDeputyPreviousCost;
use AppBundle\Entity\Report\ProfDeputyInterimCost;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait ReportProfDeputyCostsTrait
{
    /**
     * @var string
     *
     * @Assert\NotBlank(message="profDeputyCostsHowCharged.notBlank", groups={"prof-deputy-costs-how-charged"} )
     * @JMS\Type("string")
     * @JMS\Groups({"deputyCostsHowCharged"})
     */
    private $profDeputyCostsHowCharged;

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
     * @var float
     *
     * @Assert\NotBlank( message="profDeputyFixedCost.amount.notBlank", groups={"prof-deputy-fixed-cost"} )
     * @Assert\Range(min=0, minMessage = "profDeputyFixedCost.amount.minMessage", groups={"prof-deputy-fixed-cost"})
     * @JMS\Type("double")
     * @JMS\Groups({"profDeputyFixedCost"})
     */
    private $profDeputyFixedCost;

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
     * @var float
     *
     * @JMS\Type("double")
     */
    private $profDeputyTotalCosts;

    /**
     * @var float
     *
     * @JMS\Type("double")
     */
    private $profDeputyTotalCostsTakenFromClient;

    /**
     * return true if only fixed is true
     * @return boolean
     */
    public function hasProfDeputyCostsHowChargedFixedOnly()
    {
        return $this->getProfDeputyCostsHowCharged() == Report::PROF_DEPUTY_COSTS_TYPE_FIXED;
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
     * @return string
     */
    public function getProfDeputyCostsHowCharged()
    {
        return $this->profDeputyCostsHowCharged;
    }

    /**
     * @param string $profDeputyCostsHowCharged
     */
    public function setProfDeputyCostsHowCharged($profDeputyCostsHowCharged)
    {
        $this->profDeputyCostsHowCharged = $profDeputyCostsHowCharged;
        return $this;
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function profCostsInterimAtLeastOne(ExecutionContextInterface $context)
    {
        $ics = $this->getProfDeputyInterimCosts();

        foreach($ics as $index => $ic) {
            if ($ics[$index]->getDate() === null && $ics[$index]->getAmount() === null) {
                return;
            }

            if ($ics[$index]->getDate() === null) {
                $context->buildViolation('profDeputyInterimCost.date.notBlank')->atPath(sprintf('profDeputyInterimCosts[%s].date', $index))->addViolation();
            }

            if ($ics[$index]->getAmount() === null) {
                $context->buildViolation('profDeputyInterimCost.atLeastOne')->atPath(sprintf('profDeputyInterimCosts[%s].amount', $index))->addViolation();
            }
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
     * @return $this
     */
    public function setProfDeputyCostsHasPrevious($profDeputyCostsHasPrevious)
    {
        $this->profDeputyCostsHasPrevious = $profDeputyCostsHasPrevious;
        return $this;
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
     * @return $this
     */
    public function setProfDeputyPreviousCosts($profDeputyPreviousCosts)
    {
        $this->profDeputyPreviousCosts = $profDeputyPreviousCosts;
        return $this;
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
     * @return $this
     */
    public function setProfDeputyCostsHasInterim($profDeputyCostsHasInterim)
    {
        $this->profDeputyCostsHasInterim = $profDeputyCostsHasInterim;
        return $this;
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
     * @return $this
     */
    public function setProfDeputyInterimCosts($profDeputyInterimCosts)
    {
        $this->profDeputyInterimCosts = $profDeputyInterimCosts;
        return $this;
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
     * @return $this
     */
    public function setProfDeputyCostsAmountToScco($profDeputyCostsAmountToScco)
    {
        $this->profDeputyCostsAmountToScco = $profDeputyCostsAmountToScco;
        return $this;
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
     * @return $this
     */
    public function setProfDeputyCostsReasonBeyondEstimate($profDeputyCostsReasonBeyondEstimate)
    {
        $this->profDeputyCostsReasonBeyondEstimate = $profDeputyCostsReasonBeyondEstimate;
        return $this;
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
     * @return $this
     */
    public function setProfDeputyOtherCostIds($profDeputyOtherCostIds)
    {
        $this->profDeputyOtherCostIds = $profDeputyOtherCostIds;
    }

    /**
     * @return float
     */
    public function getProfDeputyFixedCost()
    {
        return $this->profDeputyFixedCost;
    }

    /**
     * @param float $profDeputyFixedCost
     * @return $this
     */
    public function setProfDeputyFixedCost($profDeputyFixedCost)
    {
        $this->profDeputyFixedCost = $profDeputyFixedCost;
        return $this;
    }

    /**
     * @return float
     */
    public function getProfDeputyTotalCosts()
    {
        return $this->profDeputyTotalCosts;
    }

    /**
     * @param float $profDeputyTotalCosts
     * @return $this
     */
    public function setProfDeputyTotalCosts($profDeputyTotalCosts)
    {
        $this->profDeputyTotalCosts = $profDeputyTotalCosts;
        return $this;
    }

    /**
     * @return float
     */
    public function getProfDeputyTotalCostsTakenFromClient()
    {
        return $this->profDeputyTotalCostsTakenFromClient;
    }

    /**
     * @param $profDeputyTotalCostsThisPeriodOnly
     */
    public function setProfDeputyTotalCostsTakenFromClient($profDeputyTotalCostsTakenFromClient)
    {
        $this->profDeputyTotalCostsTakenFromClient = $profDeputyTotalCostsTakenFromClient;
        return $this;
    }

    /**
     * @param string $typeId
     *
     * @return ProfDeputyOtherCost
     */
    protected function getProfDeputyOtherCostByTypeId($typeId)
    {
        foreach ($this->getProfDeputyOtherCosts() as $submittedCost) {

            if ($typeId == $submittedCost->getProfDeputyOtherCostTypeId()) {
                return $submittedCost;
            }
        }
    }

    /**
     * Generates a static data array of submitted costs (values set in the database). Used in the summary view.
     *
     * @return array
     */
    public function generateActualSubmittedOtherCosts()
    {
        $defaultOtherCosts = $this->getProfDeputyOtherCostTypeIds();
        $submittedCosts = [];
        foreach ($defaultOtherCosts as $defaultOtherCost) {
            $submittedCost = $this->getProfDeputyOtherCostByTypeId($defaultOtherCost['typeId']);
            $submittedCosts[$defaultOtherCost['typeId']]['typeId'] = $defaultOtherCost['typeId'];
            $submittedCosts[$defaultOtherCost['typeId']]['amount'] = !empty($submittedCost) ? $submittedCost->getAmount() : null;
            $submittedCosts[$defaultOtherCost['typeId']]['hasMoreDetails'] = $defaultOtherCost['hasMoreDetails'];
            $submittedCosts[$defaultOtherCost['typeId']]['moreDetails'] = !empty($submittedCost) ? $submittedCost->getMoreDetails() : '';

        }

        return $submittedCosts;
    }

    /**
     * Has at least one other cost been submitted? Used to determine whether section is complete as question is last
     * to be asked.
     *
     * @return bool
     */
    public function hasProfDeputyOtherCosts()
    {
        return (bool) count($this->getProfDeputyOtherCosts()) > 0;
    }
}
