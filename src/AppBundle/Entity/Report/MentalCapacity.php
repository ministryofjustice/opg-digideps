<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("none")
 */
class MentalCapacity
{
    const CAPACITY_CHANGED = 'changed';
    const CAPACITY_STAYED_SAME = 'stayedSame';

    use Traits\HasReportTrait;

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
}
