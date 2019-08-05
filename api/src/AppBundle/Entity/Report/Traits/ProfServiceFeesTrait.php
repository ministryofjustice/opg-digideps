<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\ProfServiceFee;
use AppBundle\Entity\Report\ProfServiceFeeCurrent;
use AppBundle\Entity\Report\Report;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait ProfServiceFeesTrait
{
    /**
     * @var ProfServiceFee[]
     *
     * @JMS\Groups({"report-prof-service-fees"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\ProfServiceFee", mappedBy="report", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $profServiceFees;

    public function addProfServiceFee(ProfServiceFee $profServiceFee)
    {
        if (!$this->getProfServiceFees()->contains($profServiceFee)) {
            $this->getProfServiceFees()->add($profServiceFee);
        }

        return $this;
    }

    /**
     * @return ProfServiceFee[]
     */
    public function getCurrentProfServiceFees()
    {
        return $this->getProfServiceFees()->filter(function ($profServiceFee) {
            return $profServiceFee instanceof ProfServiceFeeCurrent;
        });
    }

    /**
     * //TODO unit test
     *
     * @return bool
     */
    public function profCurrentFeesSectionCompleted()
    {
        $answeredNoFirstQuestion = $this->getCurrentProfPaymentsReceived() === 'no';

        $answeredYesAndTheRestisCompleted = $this->getCurrentProfPaymentsReceived() === 'yes'
            && count($this->getCurrentProfServiceFees()) > 0
            && !empty($this->getPreviousProfFeesEstimateGiven());

        return $answeredNoFirstQuestion || $answeredYesAndTheRestisCompleted;
    }

    /**
     * @return ProfServiceFee[]
     */
    public function getProfServiceFees()
    {
        return $this->profServiceFees;
    }

    /**
     * @param ProfServiceFee[] $profServiceFees
     */
    public function setProfServiceFees($profServiceFees)
    {
        $this->profServiceFees = $profServiceFees;
    }
}
