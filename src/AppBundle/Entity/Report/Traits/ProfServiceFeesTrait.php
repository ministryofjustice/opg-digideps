<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\Expense;
use AppBundle\Entity\Report\Fee;
use AppBundle\Entity\Report\ProfServiceFee;
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
        if (!$this->getCurrentProfServiceFees()->contains($profServiceFee)) {
            $this->getCurrentProfServiceFees()->add($profServiceFee);
        }

        return $this;
    }

    /**
     * @return ProfServiceFee[]
     */
    public function getCurrentProfServiceFees()
    {
        return $this->getProfServiceFees()->filter(function($profServiceFee) {
            return $profServiceFee->isCurrentFee();
        });
    }

    /**
     * //TODO unit test
     *
     * @return bool
     */
    public function profCurrentFeesSectionCompleted()
    {
        return count($this->getCurrentProfServiceFees()) > 0 || $this->getCurrentPaymentsReceived() === 'no';
    }

    /**
     * //TODO unit test
     *
     * @return bool
     */
    public function profCurrentFeesNotStarted()
    {
        return empty($this->getCurrentProfPaymentsReceived());
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
