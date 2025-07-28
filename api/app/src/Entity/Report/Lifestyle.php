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
     * @var int
     *
     * @JMS\Groups({"lifestyle"})
     *
     * @JMS\Type("integer")
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="lifestyle_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Report\Report", inversedBy="lifestyle")
     *
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"lifestyle"})
     *
     * @ORM\Column(name="care_appointments", type="text", nullable=true)
     */
    private $careAppointments;

    /**
     * @var string yes|no|null
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"lifestyle"})
     *
     * @ORM\Column( name="does_client_undertake_social_activities", type="string", length=4, nullable=true)
     */
    private $doesClientUndertakeSocialActivities;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"lifestyle"})
     *
     * @ORM\Column( name="activity_details_yes", type="text", nullable=true)
     */
    private $activityDetailsYes;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"lifestyle"})
     *
     * @ORM\Column( name="activity_details_no", type="text", nullable=true)
     */
    private $activityDetailsNo;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set report.
     *
     * @return Contact
     */
    public function setReport(?Report $report = null)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report.
     *
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @return string
     */
    public function getDoesClientUndertakeSocialActivities()
    {
        return $this->doesClientUndertakeSocialActivities;
    }

    /**
     * @param string $doesClientUndertakeSocialActivities
     */
    public function setDoesClientUndertakeSocialActivities($doesClientUndertakeSocialActivities)
    {
        $this->doesClientUndertakeSocialActivities = $doesClientUndertakeSocialActivities;
    }

    /**
     * @return string
     */
    public function getCareAppointments()
    {
        return $this->careAppointments;
    }

    /**
     * @param string $careAppointments
     */
    public function setCareAppointments($careAppointments)
    {
        $this->careAppointments = $careAppointments;
    }

    /**
     * @return string
     */
    public function getActivityDetailsYes()
    {
        return $this->activityDetailsYes;
    }

    /**
     * @param string $activityDetailsYes
     *
     * @return Lifestyle
     */
    public function setActivityDetailsYes($activityDetailsYes)
    {
        $this->activityDetailsYes = $activityDetailsYes;

        return $this;
    }

    /**
     * @return string
     */
    public function getActivityDetailsNo()
    {
        return $this->activityDetailsNo;
    }

    /**
     * @param string $activityDetailsNo
     *
     * @return Lifestyle
     */
    public function setActivityDetailsNo($activityDetailsNo)
    {
        $this->activityDetailsNo = $activityDetailsNo;

        return $this;
    }

    /**
     * checks if report is missing lifestyle
     * information.
     *
     * @return bool
     */
    public function missingInfo()
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
