<?php

namespace App\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="lifestyle")
 *
 * @ORM\Entity
 */
class Lifestyle
{
    /**
     * @JMS\Groups({"lifestyle"})
     * @JMS\Type("integer")
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="lifestyle_id_seq", allocationSize=1, initialValue=1)
     */
    private int $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Report\Report", inversedBy="lifestyle")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Report $report = null;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"lifestyle"})
     *
     * @ORM\Column(name="care_appointments", type="text", nullable=true)
     */
    private ?string $careAppointments = null;

    /**
     * Value is yes|no|null
     *
     * @JMS\Type("string")
     * @JMS\Groups({"lifestyle"})
     *
     * @ORM\Column( name="does_client_undertake_social_activities", type="string", length=4, nullable=true)
     */
    private ?string $doesClientUndertakeSocialActivities = null;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"lifestyle"})
     *
     * @ORM\Column( name="activity_details_yes", type="text", nullable=true)
     */
    private ?string $activityDetailsYes = null;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"lifestyle"})
     *
     * @ORM\Column( name="activity_details_no", type="text", nullable=true)
     */
    private ?string $activityDetailsNo = null;

    /**
     * Get id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set report
     */
    public function setReport(?Report $report = null): static
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report
     */
    public function getReport(): ?Report
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

    /**
     * checks if report is missing lifestyle information
     */
    public function missingInfo(): bool
    {
        if (
            empty($this->doesClientUndertakeSocialActivities)
            || empty($this->careAppointments)
            || empty($this->activityDetails)
        ) {
            return true;
        }

        return false;
    }
}
