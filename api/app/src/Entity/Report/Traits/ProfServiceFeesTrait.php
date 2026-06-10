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
     * @var Collection<int, ProfServiceFee>
     */
    #[JMS\Groups(['report-prof-service-fees'])]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: ProfServiceFee::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $profServiceFees;

    public function addProfServiceFee(ProfServiceFee $profServiceFee): static
    {
        if (!$this->getProfServiceFees()->contains($profServiceFee)) {
            $this->getProfServiceFees()->add($profServiceFee);
        }

        return $this;
    }

    /**
     * @return Collection<int, ProfServiceFee>
     */
    public function getCurrentProfServiceFees(): Collection
    {
        return $this->getProfServiceFees()->filter(function ($profServiceFee): bool {
            return $profServiceFee instanceof ProfServiceFeeCurrent;
        });
    }

    public function profCurrentFeesSectionCompleted(): bool
    {
        $answeredNoFirstQuestion = $this->getCurrentProfPaymentsReceived() === 'no';

        $answeredYesAndTheRestisCompleted = $this->getCurrentProfPaymentsReceived() === 'yes'
            && count($this->getCurrentProfServiceFees()) > 0
            && !empty($this->getPreviousProfFeesEstimateGiven());

        return $answeredNoFirstQuestion || $answeredYesAndTheRestisCompleted;
    }

    /**
     * @return Collection<int, ProfServiceFee>
     */
    public function getProfServiceFees(): Collection
    {
        return $this->profServiceFees;
    }

    /**
     * @param Collection<int, ProfServiceFee> $profServiceFees
     */
    public function setProfServiceFees(Collection $profServiceFees): void
    {
        $this->profServiceFees = $profServiceFees;
    }
}
