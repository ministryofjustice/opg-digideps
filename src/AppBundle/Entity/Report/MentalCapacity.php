<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Traits\HasReportTrait;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("none")
 */
class MentalCapacity
{
    const CAPACITY_CHANGED = 'changed';
    const CAPACITY_STAYED_SAME = 'stayedSame';

    use HasReportTrait;

    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"mental-capacity"})
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"mental-capacity"})
     * @Assert\NotBlank(message="mentalCapacity.hasCapacityChanged.notBlank", groups={"capacity"})
     */
    private $hasCapacityChanged;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"mental-capacity"})
     * 
     * @Assert\NotBlank(message="mentalCapacity.hasCapacityChangedDetails.notBlank", groups={"has-capacity-changed-yes"})
     */
    private $hasCapacityChangedDetails;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"mental-capacity"})
     *
     * @Assert\NotBlank(message="mentalCapacity.mentalAssessmentDate.notBlank", groups={"capacity-assessment"})
     */
    private $mentalAssessmentDate = null;

    public function getId()
    {
        return $this->id;
    }

    public function getHasCapacityChanged()
    {
        return $this->hasCapacityChanged;
    }

    public function getHasCapacityChangedDetails()
    {
        return $this->hasCapacityChangedDetails;
    }

    public function setHasCapacityChanged($hasCapacityChanged)
    {
        $this->hasCapacityChanged = $hasCapacityChanged;

        return $this;
    }

    public function setHasCapacityChangedDetails($hasCapacityChangedDetails)
    {
        $this->hasCapacityChangedDetails = $hasCapacityChangedDetails;

        return $this;
    }

    public function getMentalAssessmentDate()
    {
        return $this->mentalAssessmentDate;
    }

    public function setMentalAssessmentDate($mentalAssessmentDate)
    {
        $this->mentalAssessmentDate = $mentalAssessmentDate;

        return $this;
    }
}
