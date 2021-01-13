<?php

namespace App\Entity\Report;

use App\Entity\Report\Traits\HasReportTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @JMS\Groups({"mental-assessment-date"})
     *
     * @Assert\NotBlank(message="mentalCapacity.mentalAssessmentDate.notBlank", groups={"mental-assessment-date"})
     */
    private $mentalAssessmentDate;

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
