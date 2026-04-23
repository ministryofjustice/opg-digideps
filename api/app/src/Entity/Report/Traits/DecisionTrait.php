<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Report\Decision;
use OPG\Digideps\Backend\Entity\Report\Report;

trait DecisionTrait
{
    /**
     * @var Collection<Decision>
     */
    #[JMS\Groups(['decision'])]
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\Decision>')]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: Decision::class, cascade: ['persist', 'remove'])]
    private $decisions;

    /**
     * @var string deputy reason for not having decision. Required if no decisions are added
     **/
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'decision'])]
    #[ORM\Column(name: 'reason_for_no_decisions', type: 'text', nullable: true)]
    private $reasonForNoDecisions;

    /**
     * Get decisions.
     *
     * @return Collection<Decision>
     */
    public function getDecisions()
    {
        return $this->decisions;
    }

    /**
     * Add decisions.
     *
     * @return Report
     */
    public function addDecision(Decision $decision)
    {
        $this->decisions[] = $decision;

        return $this;
    }

    /**
     * Remove decisions.
     */
    public function removeDecision(Decision $decision)
    {
        $this->decisions->removeElement($decision);
    }

    /**
     * Set reasonForNoDecisions.
     *
     * @param string $reasonForNoDecisions
     *
     * @return Report
     **/
    public function setReasonForNoDecisions($reasonForNoDecisions)
    {
        if (is_string($reasonForNoDecisions)) {
            $this->reasonForNoDecisions = trim($reasonForNoDecisions, " \n");
        } else {
            $this->reasonForNoDecisions = null;
        }

        return $this;
    }

    /**
     * Get ReasonForNoDecisions.
     *
     * @return string
     */
    public function getReasonForNoDecisions()
    {
        return $this->reasonForNoDecisions;
    }
}
