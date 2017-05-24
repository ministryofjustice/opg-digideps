<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\Decision;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait DecisionTrait
{
    /**
     * @var Decision[]
     *
     * @JMS\Groups({"decision"})
     * @JMS\Type("array<AppBundle\Entity\Report\Decision>")
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\Decision", mappedBy="report", cascade={"persist"})
     */
    private $decisions;


    /**
     * @var string deputy reason for not having decision. Required if no decisions are added
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report","decision"})
     * @ORM\Column(name="reason_for_no_decisions", type="text", nullable=true)
     **/
    private $reasonForNoDecisions;

    /**
     * Get decisions.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDecisions()
    {
        return $this->decisions;
    }

    /**
     * Add decisions.
     *
     * @param Decision $decision
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
     *
     * @param Decision $decision
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
        $this->reasonForNoDecisions = trim($reasonForNoDecisions, " \n");

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
