<?php

namespace OPG\Digideps\Frontend\Entity\Report;

use OPG\Digideps\Frontend\Entity\Report\Traits\HasReportTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Action
{
    use HasReportTrait;

    /**
     * @JMS\Type("integer")
     */
    private int $id;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"action"})
     *
     * @Assert\NotBlank(message="action.doYouExpectFinancialDecisions.notBlank", groups={"action-expect-decisions-choice"})
     */
    private ?string $doYouExpectFinancialDecisions = null;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"action"})
     *
     * @Assert\NotBlank(message="action.doYouExpectFinancialDecisionsDetails.notBlank", groups={"action-expect-decisions-details"})
     */
    private ?string $doYouExpectFinancialDecisionsDetails = null;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"action"})
     *
     * @Assert\NotBlank(message="action.doYouHaveConcerns.notBlank", groups={"action-have-concerns-choice"})
     */
    private ?string $doYouHaveConcerns = null;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"action"})
     *
     * @Assert\NotBlank(message="action.doYouHaveConcernsDetails.notBlank", groups={"action-have-concerns-details"})
     */
    private ?string $doYouHaveConcernsDetails = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getDoYouExpectFinancialDecisions(): ?string
    {
        return $this->doYouExpectFinancialDecisions;
    }

    public function setDoYouExpectFinancialDecisions(?string $doYouExpectFinancialDecisions): static
    {
        $this->doYouExpectFinancialDecisions = $doYouExpectFinancialDecisions;

        return $this;
    }

    public function getDoYouExpectFinancialDecisionsDetails(): ?string
    {
        return $this->doYouExpectFinancialDecisionsDetails;
    }

    public function setDoYouExpectFinancialDecisionsDetails(?string $doYouExpectFinancialDecisionsDetails): static
    {
        $this->doYouExpectFinancialDecisionsDetails = $doYouExpectFinancialDecisionsDetails;

        return $this;
    }

    public function getDoYouHaveConcerns(): ?string
    {
        return $this->doYouHaveConcerns;
    }

    public function setDoYouHaveConcerns(?string $doYouHaveConcerns): static
    {
        $this->doYouHaveConcerns = $doYouHaveConcerns;

        return $this;
    }

    public function getDoYouHaveConcernsDetails(): ?string
    {
        return $this->doYouHaveConcernsDetails;
    }

    public function setDoYouHaveConcernsDetails(?string $doYouHaveConcernsDetails): static
    {
        $this->doYouHaveConcernsDetails = $doYouHaveConcernsDetails;

        return $this;
    }
}
