<?php

namespace App\Entity\Report\Traits;

use Doctrine\Common\Collections\Collection;
use App\Entity\Report\Decision;
use App\Entity\Report\Report;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait DecisionTrait
{
    /**
     * @var Decision[]
     *
     * @JMS\Groups({"decision"})
     *
     * @JMS\Type("ArrayCollection<App\Entity\Report\Decision>")
     */
    #[ORM\OneToMany(targetEntity: Decision::class, mappedBy: 'report', cascade: ['persist', 'remove'])]
    private $decisions;

    /**
     * @var string deputy reason for not having decision. Required if no decisions are added
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report","decision"})
     **/
    #[ORM\Column(name: 'reason_for_no_decisions', type: 'text', nullable: true)]
    private $reasonForNoDecisions;

    /**
     * Get decisions.
     *
     * @return Collection
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
