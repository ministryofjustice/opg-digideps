<?php

namespace App\Entity\Report;

use App\Entity\Report\Traits\HasReportTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Lifestyle
{
    use HasReportTrait;

    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"lifestyle"})
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"lifestyle"})
     *
     * @Assert\NotBlank(message="lifestyle.careAppointments.notBlank", groups={"lifestyle-care-appointments"})
     */
    private $careAppointments;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"lifestyle"})
     *
     * @Assert\NotBlank(message="lifestyle.doesClientUndertakeSocialActivities.notBlank", groups={"lifestyle-undertake-social-activities"})
     */
    private $doesClientUndertakeSocialActivities;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"lifestyle"})
     *
     * @Assert\NotBlank(message="lifestyle.activityDetailsYes.notBlank", groups={"lifestyle-activity-details-yes"})
     */
    private $activityDetailsYes;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"lifestyle"})
     *
     * @Assert\NotBlank(message="lifestyle.activityDetailsNo.notBlank", groups={"lifestyle-activity-details-no"})
     */
    private $activityDetailsNo;

    /**
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCareAppointments()
    {
        return $this->careAppointments;
    }

    /**
     * @param mixed $careAppointments
     */
    public function setCareAppointments($careAppointments)
    {
        $this->careAppointments = $careAppointments;
    }

    /**
     * @return mixed
     */
    public function getDoesClientUndertakeSocialActivities()
    {
        return $this->doesClientUndertakeSocialActivities;
    }

    /**
     * @param mixed $doesClientUndertakeSocialActivities
     */
    public function setDoesClientUndertakeSocialActivities($doesClientUndertakeSocialActivities)
    {
        $this->doesClientUndertakeSocialActivities = $doesClientUndertakeSocialActivities;
    }

    /**
     * @return bool
     */
    public function keepOnlyRelevantLifestyleData()
    {
        return true;
    }

    /**
     * @return mixed
     */
    public function getActivityDetailsYes()
    {
        return $this->activityDetailsYes;
    }

    /**
     * @param mixed $activityDetailsYes
     */
    public function setActivityDetailsYes($activityDetailsYes)
    {
        $this->activityDetailsYes = $activityDetailsYes;
    }

    /**
     * @return mixed
     */
    public function getActivityDetailsNo()
    {
        return $this->activityDetailsNo;
    }

    /**
     * @param mixed $activityDetailsNo
     */
    public function setActivityDetailsNo($activityDetailsNo)
    {
        $this->activityDetailsNo = $activityDetailsNo;
    }
}
