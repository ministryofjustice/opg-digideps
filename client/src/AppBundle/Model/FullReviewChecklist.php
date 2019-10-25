<?php declare(strict_types=1);

namespace AppBundle\Model;

use JMS\Serializer\Annotation as JMS;

class FullReviewChecklist
{
    /**
     * @var string
     * @JMS\Groups({"full-review-checklist"})
     * @JMS\Type("string")
     */
    private $decisionExplanation;

    /**
     * @return string
     */
    public function getDecisionExplanation(): ?string
    {
        return $this->decisionExplanation;
    }

    /**
     * @param string $decision
     */
    public function setDecisionExplanation($decisionExplanation)
    {
        $this->decisionExplanation = $decisionExplanation;
        return $this;
    }
}
