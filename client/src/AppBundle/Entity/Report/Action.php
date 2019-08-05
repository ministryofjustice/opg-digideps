<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Report\Traits\HasReportTrait;
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

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Action
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDoYouExpectFinancialDecisions()
    {
        return $this->doYouExpectFinancialDecisions;
    }

    /**
     * @param mixed $doYouExpectFinancialDecisions
     *
     * @return Action
     */
    public function setDoYouExpectFinancialDecisions($doYouExpectFinancialDecisions)
    {
        $this->doYouExpectFinancialDecisions = $doYouExpectFinancialDecisions;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDoYouExpectFinancialDecisionsDetails()
    {
        return $this->doYouExpectFinancialDecisionsDetails;
    }

    /**
     * @param mixed $doYouExpectFinancialDecisionsDetails
     *
     * @return Action
     */
    public function setDoYouExpectFinancialDecisionsDetails($doYouExpectFinancialDecisionsDetails)
    {
        $this->doYouExpectFinancialDecisionsDetails = $doYouExpectFinancialDecisionsDetails;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDoYouHaveConcerns()
    {
        return $this->doYouHaveConcerns;
    }

    /**
     * @param mixed $doYouHaveConcerns
     *
     * @return Action
     */
    public function setDoYouHaveConcerns($doYouHaveConcerns)
    {
        $this->doYouHaveConcerns = $doYouHaveConcerns;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDoYouHaveConcernsDetails()
    {
        return $this->doYouHaveConcernsDetails;
    }

    /**
     * @param mixed $doYouHaveConcernsDetails
     *
     * @return Action
     */
    public function setDoYouHaveConcernsDetails($doYouHaveConcernsDetails)
    {
        $this->doYouHaveConcernsDetails = $doYouHaveConcernsDetails;

        return $this;
    }
}
