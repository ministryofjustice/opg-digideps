<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Report\Decision;

trait DecisionTrait
{
    /**
     * @var Collection<int, Decision>
     */
    #[JMS\Groups(['decision'])]
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\Decision>')]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: Decision::class, cascade: ['persist', 'remove'])]
    private Collection $decisions;

    /**
     * Deputy reason for not having decision. Required if no decisions are added.
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'decision'])]
    #[ORM\Column(name: 'reason_for_no_decisions', type: 'text', nullable: true)]
    private ?string $reasonForNoDecisions = null;

    /**
     * @return Collection<int, Decision>
     */
    public function getDecisions(): Collection
    {
        return $this->decisions;
    }

    public function addDecision(Decision $decision): static
    {
        $this->decisions[] = $decision;

        return $this;
    }

    public function removeDecision(Decision $decision): void
    {
        $this->decisions->removeElement($decision);
    }

    public function setReasonForNoDecisions(?string $reasonForNoDecisions): static
    {
        if (is_string($reasonForNoDecisions)) {
            $this->reasonForNoDecisions = trim($reasonForNoDecisions, " \n");
        } else {
            $this->reasonForNoDecisions = null;
        }

        return $this;
    }

    public function getReasonForNoDecisions(): ?string
    {
        return $this->reasonForNoDecisions;
    }
}
