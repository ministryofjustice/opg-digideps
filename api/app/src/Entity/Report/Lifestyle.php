<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

#[ORM\Table(name: 'lifestyle')]
#[ORM\Entity]
class Lifestyle
{
    #[JMS\Groups(['lifestyle'])]
    #[JMS\Type('integer')]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'lifestyle_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\OneToOne(inversedBy: 'lifestyle', targetEntity: Report::class)]
    private Report $report;

    #[JMS\Type('string')]
    #[JMS\Groups(['lifestyle'])]
    #[ORM\Column(name: 'care_appointments', type: 'text', nullable: true)]
    private ?string $careAppointments = null;

    /**
     * Value is yes|no|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['lifestyle'])]
    #[ORM\Column(name: 'does_client_undertake_social_activities', type: 'string', length: 4, nullable: true)]
    private ?string $doesClientUndertakeSocialActivities = null;


    #[JMS\Type('string')]
    #[JMS\Groups(['lifestyle'])]
    #[ORM\Column(name: 'activity_details_yes', type: 'text', nullable: true)]
    private ?string $activityDetailsYes = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['lifestyle'])]
    #[ORM\Column(name: 'activity_details_no', type: 'text', nullable: true)]
    private ?string $activityDetailsNo = null;

    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function setId(int $id): static
    {
        if ($this->id === null) {
            $this->id = $id;
        } elseif ($id === 0) {
            throw new \DomainException('You may not set the id of an entity to zero.');
        } else {
            throw new \LogicException('You may not set the id of an entity more than once.');
        }

        return $this;
    }

    public function setReport(Report $report): static
    {
        $this->report = $report;

        return $this;
    }

    public function getReport(): Report
    {
        return $this->report;
    }

    public function getDoesClientUndertakeSocialActivities(): ?string
    {
        return $this->doesClientUndertakeSocialActivities;
    }

    public function setDoesClientUndertakeSocialActivities(?string $doesClientUndertakeSocialActivities): void
    {
        $this->doesClientUndertakeSocialActivities = $doesClientUndertakeSocialActivities;
    }

    public function getCareAppointments(): ?string
    {
        return $this->careAppointments;
    }

    public function setCareAppointments(?string $careAppointments): void
    {
        $this->careAppointments = $careAppointments;
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
