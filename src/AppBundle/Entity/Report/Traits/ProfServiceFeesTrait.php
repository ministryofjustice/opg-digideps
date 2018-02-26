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
     * @var string yes|no|null
     *
     * @JMS\Type("string")
     * @JMS\Groups({"prof-service-fees"})
     * @ORM\Column(name="current_prof_payments_received", type="string", length=3, nullable=true)
     */
    private $currentProfPaymentsReceived;

    /**
     * @var ProfServiceFee[]
     *
     * @JMS\Groups({"prof-service-fees"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\ProfServiceFee", mappedBy="report", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $profServiceFees;

    /**
     * @return string
     */
    public function getCurrentProfPaymentsReceived()
    {
        return $this->currentProfPaymentsReceived;
    }

    /**
     * @param string $currentProfPaymentsReceived
     */
    public function setCurrentProfPaymentsReceived($currentProfPaymentsReceived)
    {
        $this->currentProfPaymentsReceived = $currentProfPaymentsReceived;
    }

    public function addProfServiceFee(ProfServiceFee $profServiceFee)
    {
        if (!$this->profServicefees->contains($profServiceFee)) {
            $this->profServicefees->add($profServiceFee);
        }

        return $this;
    }

    /**
     * @return ProfServiceFee[]
     */
    public function getCurrentProfServiceFees()
    {
        return $this->profServicefees->filter(function($profServiceFee) {
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
        return true;
//        return 0 === count($this->getProfCurrentFeesWithValidAmount())
//            && empty($this->getReasonForNoCurrentFees())
//            && 0 === count($this->getExpenses())
//            && empty($this->getPaidForAnything());
    }


}
