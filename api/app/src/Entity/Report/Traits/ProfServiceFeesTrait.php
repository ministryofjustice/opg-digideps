<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Report\ProfServiceFee;
use OPG\Digideps\Backend\Entity\Report\ProfServiceFeeCurrent;

trait ProfServiceFeesTrait
{
    /**
     * @var Collection<ProfServiceFee>
     */
    #[JMS\Groups(['report-prof-service-fees'])]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: ProfServiceFee::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $profServiceFees;

    public function addProfServiceFee(ProfServiceFee $profServiceFee)
    {
        if (!$this->getProfServiceFees()->contains($profServiceFee)) {
            $this->getProfServiceFees()->add($profServiceFee);
        }

        return $this;
    }

    /**
     * @return Collection<ProfServiceFee>
     */
    public function getCurrentProfServiceFees()
    {
        return $this->getProfServiceFees()->filter(function ($profServiceFee) {
            return $profServiceFee instanceof ProfServiceFeeCurrent;
        });
    }

    /**
     * //TODO unit test.
     *
     * @return bool
     */
    public function profCurrentFeesSectionCompleted()
    {
        $answeredNoFirstQuestion = 'no' === $this->getCurrentProfPaymentsReceived();

        $answeredYesAndTheRestisCompleted = 'yes' === $this->getCurrentProfPaymentsReceived()
            && count($this->getCurrentProfServiceFees()) > 0
            && !empty($this->getPreviousProfFeesEstimateGiven());

        return $answeredNoFirstQuestion || $answeredYesAndTheRestisCompleted;
    }

    /**
     * @return Collection<ProfServiceFee>
     */
    public function getProfServiceFees()
    {
        return $this->profServiceFees;
    }

    /**
     * @param Collection<ProfServiceFee> $profServiceFees
     */
    public function setProfServiceFees($profServiceFees)
    {
        $this->profServiceFees = $profServiceFees;
    }
}
