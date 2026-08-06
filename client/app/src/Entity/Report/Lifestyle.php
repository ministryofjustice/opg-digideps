<?php

namespace OPG\Digideps\Frontend\Entity\Report;

use OPG\Digideps\Frontend\Entity\Report\Traits\HasReportTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Lifestyle
{
    use HasReportTrait;

    /**
     * @var int
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['lifestyle'])]
    private $id;

    #[JMS\Type('string')]
    #[JMS\Groups(['lifestyle'])]
    #[Assert\NotBlank(message: 'lifestyle.careAppointments.notBlank', groups: ['lifestyle-care-appointments'])]
    private $careAppointments;

    #[JMS\Type('string')]
    #[JMS\Groups(['lifestyle'])]
    #[Assert\NotBlank(message: 'lifestyle.doesClientUndertakeSocialActivities.notBlank', groups: ['lifestyle-undertake-social-activities'])]
    private $doesClientUndertakeSocialActivities;

    #[JMS\Type('string')]
    #[JMS\Groups(['lifestyle'])]
    #[Assert\NotBlank(message: 'lifestyle.activityDetailsYes.notBlank', groups: ['lifestyle-activity-details-yes'])]
    private $activityDetailsYes;

    #[JMS\Type('string')]
    #[JMS\Groups(['lifestyle'])]
    #[Assert\NotBlank(message: 'lifestyle.activityDetailsNo.notBlank', groups: ['lifestyle-activity-details-no'])]
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
    public function setId($id): static
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
    public function setCareAppointments($careAppointments): static
    {
        $this->careAppointments = $careAppointments;

        return $this;
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
    public function setDoesClientUndertakeSocialActivities($doesClientUndertakeSocialActivities): static
    {
        $this->doesClientUndertakeSocialActivities = $doesClientUndertakeSocialActivities;

        return $this;
    }

    /**
     * @return bool
     */
    public function keepOnlyRelevantLifestyleData(): bool
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
    public function setActivityDetailsYes($activityDetailsYes): static
    {
        $this->activityDetailsYes = $activityDetailsYes;

        return $this;
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
    public function setActivityDetailsNo($activityDetailsNo): static
    {
        $this->activityDetailsNo = $activityDetailsNo;

        return $this;
    }
}
