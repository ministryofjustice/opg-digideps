<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\Traits\HasReportTrait;
use Symfony\Component\Validator\Constraints as Assert;

class Lifestyle
{
    use HasReportTrait;

    #[JMS\Type('integer')]
    #[JMS\Groups(['lifestyle'])]
    private ?int $id = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['lifestyle'])]
    #[Assert\NotBlank(message: 'lifestyle.careAppointments.notBlank', groups: ['lifestyle-care-appointments'])]
    private ?string $careAppointments = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['lifestyle'])]
    #[Assert\NotBlank(message: 'lifestyle.doesClientUndertakeSocialActivities.notBlank', groups: ['lifestyle-undertake-social-activities'])]
    private ?string $doesClientUndertakeSocialActivities = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['lifestyle'])]
    #[Assert\NotBlank(message: 'lifestyle.activityDetailsYes.notBlank', groups: ['lifestyle-activity-details-yes'])]
    private ?string $activityDetailsYes = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['lifestyle'])]
    #[Assert\NotBlank(message: 'lifestyle.activityDetailsNo.notBlank', groups: ['lifestyle-activity-details-no'])]
    private ?string $activityDetailsNo = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getCareAppointments(): ?string
    {
        return $this->careAppointments;
    }

    public function setCareAppointments(?string $careAppointments): static
    {
        $this->careAppointments = $careAppointments;
        return $this;
    }

    public function getDoesClientUndertakeSocialActivities(): ?string
    {
        return $this->doesClientUndertakeSocialActivities;
    }

    public function setDoesClientUndertakeSocialActivities(?string $doesClientUndertakeSocialActivities): static
    {
        $this->doesClientUndertakeSocialActivities = $doesClientUndertakeSocialActivities;
        return $this;
    }

    public function getActivityDetailsYes(): ?string
    {
        return $this->activityDetailsYes;
    }

    public function setActivityDetailsYes(?string $activityDetailsYes): static
    {
        $this->activityDetailsYes = $activityDetailsYes;
        return $this;
    }

    public function getActivityDetailsNo(): ?string
    {
        return $this->activityDetailsNo;
    }

    public function setActivityDetailsNo(?string $activityDetailsNo): static
    {
        $this->activityDetailsNo = $activityDetailsNo;
        return $this;
    }
}
