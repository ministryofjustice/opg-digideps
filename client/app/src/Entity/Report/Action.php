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
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"action"})
     *
     * @Assert\NotBlank(message="action.doYouExpectFinancialDecisions.notBlank", groups={"action-expect-decisions-choice"})
     */
    private $doYouExpectFinancialDecisions;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"action"})
     *
     * @Assert\NotBlank(message="action.doYouExpectFinancialDecisionsDetails.notBlank", groups={"action-expect-decisions-details"})
     */
    private $doYouExpectFinancialDecisionsDetails;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"action"})
     *
     * @Assert\NotBlank(message="action.doYouHaveConcerns.notBlank", groups={"action-have-concerns-choice"})
     */
    private $doYouHaveConcerns;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"action"})
     *
     * @Assert\NotBlank(message="action.doYouHaveConcernsDetails.notBlank", groups={"action-have-concerns-details"})
     */
    private $doYouHaveConcernsDetails;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getDoYouExpectFinancialDecisions(): mixed
    {
        return $this->doYouExpectFinancialDecisions;
    }

    public function setDoYouExpectFinancialDecisions(mixed $doYouExpectFinancialDecisions): static
    {
        $this->doYouExpectFinancialDecisions = $doYouExpectFinancialDecisions;

        return $this;
    }

    public function getDoYouExpectFinancialDecisionsDetails(): mixed
    {
        return $this->doYouExpectFinancialDecisionsDetails;
    }

    public function setDoYouExpectFinancialDecisionsDetails(mixed $doYouExpectFinancialDecisionsDetails): static
    {
        $this->doYouExpectFinancialDecisionsDetails = $doYouExpectFinancialDecisionsDetails;

        return $this;
    }

    public function getDoYouHaveConcerns(): mixed
    {
        return $this->doYouHaveConcerns;
    }

    public function setDoYouHaveConcerns(mixed $doYouHaveConcerns): static
    {
        $this->doYouHaveConcerns = $doYouHaveConcerns;

        return $this;
    }

    public function getDoYouHaveConcernsDetails(): mixed
    {
        return $this->doYouHaveConcernsDetails;
    }

    public function setDoYouHaveConcernsDetails(mixed $doYouHaveConcernsDetails): static
    {
        $this->doYouHaveConcernsDetails = $doYouHaveConcernsDetails;

        return $this;
    }
}
