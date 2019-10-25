<?php declare(strict_types=1);


namespace AppBundle\Model;

class FullReviewChecklist
{
    /**
     * @var string
     */
    private $decision;

    /**
     * @return string
     */
    public function getDecision(): ?string
    {
        return $this->decision;
    }

    /**
     * @param string $decision
     */
    public function setDecision(string $decision)
    {
        $this->decision = $decision;
        return $this;
    }
}
